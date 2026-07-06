# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

**Sastrería Medina** — Laravel 11 (PHP 8.2) point-of-sale and rental management system for a tailoring/clothing shop. Domain language and code identifiers are in **Spanish**. Server-rendered Blade + **Livewire 3** UI, styled with **Tailwind 4** via Vite (`resources/js/app.js`, `resources/css/app.css`) — this project **does** have a `package.json`/Vite build step, don't skip it. Thermal ticket printing via ESC/POS (network connector), PDF invoices/etiquetas via DomPDF.

## Commands

```bash
composer install                 # install PHP deps
pnpm install                      # install JS deps (Vite/Tailwind)
cp .env.example .env             # first-time setup
php artisan key:generate
php artisan migrate --seed        # DB is MySQL (see .env DB_* — phpunit.xml and migrations use MySQL-specific SQL, sqlite needs driver-conditional workarounds)
pnpm build                        # build Tailwind/Vite assets (needed once for @vite to resolve; rm -f public/hot after)
php artisan serve                 # dev server at http://localhost:8000

php artisan test                  # run full PHPUnit suite
php artisan test --filter=Name    # run a single test / test method
vendor/bin/pint --dirty           # format changed files (Laravel Pint)
php artisan tinker                # REPL
```

## Architecture

Standard Laravel MVC. Routes → thin-ish controllers → Eloquent models → Blade views. All routes live in `routes/web.php` (no `api.php` usage). Auth aliases for `spatie/laravel-permission` (`role`, `permission`, `role_or_permission`) are registered in `bootstrap/app.php`.

### Core domains (models + controllers)

- **Alquileres** (rentals): `Alquiler` ↔ physical garment units. `StockAlquiler` (prenda) → many `TalleStock` (talle) → many `UnidadStock` (unidad física, con `codigo` `ALQ-nnnnnnn` y `estado` disponible/alquilada/baja — fuente de verdad de disponibilidad real). `Alquiler::unidades()` (pivote `alquiler_unidad`, con `precio` snapshot) reemplaza el viejo `alquiler_stock` por-cantidad. `AlquilerService::asignarUnidades()`/`asignarUnidadEspecifica()`/`liberarUnidades()` centralizan la asignación/liberación de unidades y las reusan `ReservaService::convertirAAlquiler` y `DevolucionService`. Rentals carry a `garantia` (deposit) and `estado`. Returns go through `DevolucionService::procesar` (single guarded path, computes **multas**/late-return fines).
- **Reservas** (reservations): `Reserva` can be converted into an `Alquiler` (`ReservaService::convertirAAlquiler`). Reservas stay talle/cantidad-level (no unit assigned yet) — decrements `cantidad_reservada` on `TalleStock`; the physical unit is only picked at conversion time.
- **Ventas** (sales): `Venta` → many `DetalleVenta`, each pointing at a `ProductoTalle` (`producto_talle_id`, snapshot de nombre/talle/precio). Stock (`cantidad_disponible` on the talle) is decremented on store, and **restored** on update/destroy.
- **Productos**: `Producto` (`tipo`: `comprado` | `fabricado`) → many `ProductoTalle` (per-size stock: `cantidad_total`/`cantidad_disponible`/`cantidad_vendida`, + `codigo_barra` auto-generated `PRD-nnnnnnn` or manual EAN). Only `Producto` with `activo_para_venta = true` are sellable.
- **Cierre de caja** (cash close): `CierreCajaController`/`ReporteCajaService` aggregate ventas/alquileres/gastos by day/week/month and export PDF.
- **Configuracion**: key/value settings (`nombre`/`descripcion`/`valor`) stored in DB, editable via `configuraciones.*` routes — used for business params like fine rates.
- **Códigos de barra**: ver sección dedicada más abajo.

### Key conventions

- **Stock mutations are wrapped in `DB::transaction()`, most with `lockForUpdate()` on the row being decremented.** When touching sales/rentals/returns, preserve this: decrement on create, restore original quantities before re-applying on update, restore on delete. Getting this wrong corrupts inventory.
- Controllers/Livewire components are inconsistently cased in places (`loginController.php`, `homeController.php` vs `AlquilerService.php`) but all classes are `StudlyCase`. Match the existing file when editing; don't rename.
- `VentaService`/`AlquilerService` etc. log via `Log::` and services return Spanish success/error strings or throw `ValidationException` — follow that pattern for user-facing feedback (Livewire components turn these into flash messages or `addError`).
- **DomPDF `allowed_protocols` must include `data://`** (`config/dompdf.php`) — without it, any `<img src="data:image/png;base64,...">` (barcodes) fails silently (no exception, falls back to rendering the `alt` text). This is a real bug that was fixed once; don't let it regress if `config/dompdf.php` gets republished/reset.

### Printing & PDF

- `App\Services\TicketPrinterService` wraps `mike42/escpos-php`. Constructor takes `(string $printerConfig, bool $isTestMode, int $printerPort = 9100)`. **Test mode** (`isTestMode: true`) writes the ticket to `storage/app/tickets/*.txt` instead of a physical printer; production mode connects to a **network** thermal printer via `NetworkPrintConnector` (IP:port, port 9100 = raw/JetDirect). Config comes from `config/services.php` → `PRINTER_TEST_MODE`/`PRINTER_HOST`/`PRINTER_PORT` env vars, read in `VentaService::imprimirTicket`. (There is no Windows printer support anymore — the app runs in a Linux container in production.)
- Invoices/reports/etiquetas use `barryvdh/laravel-dompdf` (`FacturaController`, `EtiquetaController`, `CierreCajaController` PDF exports).

### Códigos de barra

Ver `docs/barcode-spec.md` para el diseño completo (fases 0-4). Resumen de la convención (Fase 0, ya implementada):

- **El lector es un teclado** (HID): escanea → tipea el código → Enter. No requiere driver ni SDK.
- **Prefijos** (padding a 7 dígitos, fijo):
  - `PRD-` + `producto_talle_id` → código de venta (SKU), generado o EAN de fábrica si el comprado lo trae.
  - `ALQ-` + `unidad_stock_id` → código de unidad física de alquiler.
  - Solo dígitos → EAN de fábrica, siempre resuelve a **ventas** (alquiler nunca usa EAN puro).
- `App\Services\BarcodeService`: `generarCodigoVenta()`, `generarCodigoUnidad()`, `parsear()` (clasifica por prefijo/formato, lanza `InvalidArgumentException` si no reconoce el código), `pngBase64()` (Code128 para `PRD-`/`ALQ-`, EAN-13 para dígitos crudos, vía `picqer/php-barcode-generator`).

### Auth

Custom login (`loginController` using `Auth::validate` + `Auth::login`), no Breeze/Jetstream. Roles/permissions via `spatie/laravel-permission` (`users`, `roles`, `profiles` resource routes). Note: most business routes are **not** currently guarded by `auth`/`role` middleware — add guards deliberately if asked, don't assume they exist.

### Deployment (Docker / Coolify)

- Multi-stage `Dockerfile` (composer → node/pnpm assets → PHP-FPM+Nginx+Supervisor on `php:8.2-fpm-alpine`), built and tested locally with `docker build` end-to-end (real MySQL, migrations, login, Vite assets, PDF/barcode generation all verified working inside the container).
- `docker/entrypoint.sh` runs `config:cache`/`route:cache`/`view:cache` then `migrate --force` on every container start (toggle via `RUN_MIGRATIONS=false` env var if you want manual migrations instead).
- `.env.example` documents all required production env vars, including `PRINTER_HOST`/`PRINTER_PORT`/`PRINTER_TEST_MODE`.
- **`composer.json` pins `"config": {"platform": {"php": "8.2.99"}}`** — this project's dev machine may run a newer local PHP (e.g. 8.4) than the Docker image (8.2). Without this pin, `composer update`/`require` on a newer local PHP can silently resolve dependency versions (seen with `symfony/*` packages) that require PHP 8.4+, breaking the 8.2 production image with `platform_check.php` errors. Keep this pin; if bumping the Docker image's PHP version, bump this too.
