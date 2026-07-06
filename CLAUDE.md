# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

**Sastrería Medina** — Laravel 11 (PHP 8.2) point-of-sale and rental management system for a tailoring/clothing shop. Domain language and code identifiers are in **Spanish**. Server-rendered Blade UI (no JS build step — no `package.json`/Vite). Thermal ticket printing via ESC/POS, PDF invoices via DomPDF.

## Commands

```bash
composer install                 # install PHP deps
cp .env.example .env             # first-time setup
php artisan key:generate
php artisan migrate               # DB is SQLite by default (database/database.sqlite)
php artisan serve                 # dev server at http://localhost:8000

php artisan test                  # run full PHPUnit suite
php artisan test --filter=Name    # run a single test / test method
vendor/bin/pint                   # format code (Laravel Pint)
php artisan tinker                # REPL
```

There is no `pnpm`/npm workflow here — the UI is pure Blade, so ignore the global "use pnpm" rule for this repo.

## Architecture

Standard Laravel MVC. Routes → thin-ish controllers → Eloquent models → Blade views. All routes live in `routes/web.php` (no `api.php` usage). Auth aliases for `spatie/laravel-permission` (`role`, `permission`, `role_or_permission`) are registered in `bootstrap/app.php`.

### Core domains (models + controllers)

- **Alquileres** (rentals): `Alquiler` ↔ `StockAlquiler` via `alquiler_stock` pivot (with `talle_id`, `cantidad`). Rentals carry a `garantia` (deposit) and `estado`. Returns are handled by `DevolucionController` (`Devolucion` model) which computes **multas** (late-return fines).
- **Reservas** (reservations): `Reserva` can be converted into an `Alquiler` (`ReservaController@convertirAAlquiler`). Reserving decrements `cantidad_reservada` on `TalleStock`.
- **Ventas** (sales): `Venta` → many `DetalleVenta`. A detalle's `tipo_producto` is either `compra` or `manual`, and `producto_id`/`talle_id` point at either `Compra`/`TalleCompra` or `ProductoVenta`/`TalleProductoVenta` accordingly. This polymorphic-by-string pattern is repeated in every ventas method — stock (`cantidad_disponible` on the talle) is decremented on store, and **restored** on update/destroy.
- **Compras** (purchases/inventory): `Compra` → many `TalleCompra` (per-size stock: `cantidad_total`/`cantidad_disponible`/`cantidad_vendida`). Only `Compra` with `activo_para_venta = true` are sellable.
- **Cierre de caja** (cash close): `CierreCajaController` aggregates ventas/alquileres/gastos by day/week/month and exports PDF.
- **Configuracion**: key/value settings (`nombre`/`descripcion`/`valor`) stored in DB, editable via `configuraciones.*` routes — used for business params like fine rates.

### Key conventions

- **Stock mutations are wrapped in `DB::beginTransaction()` … `commit`/`rollback`.** When touching sales/rentals/returns, preserve this: decrement on create, restore original quantities before re-applying on update, restore on delete. Getting this wrong corrupts inventory.
- **`tipo_producto` branching**: any code reading a `DetalleVenta` must branch on `compra` vs `manual` to resolve the right model — grep for existing `if ($... === 'compra')` blocks as the template.
- Controllers are inconsistently cased (`ventaController.php`, `homeController.php` vs `AlquilerController.php`) but all classes are `StudlyCase`. Match the existing file when editing; don't rename.
- `VentaController::store` logs verbosely via `Log::info` and returns Spanish `success`/`error` flash messages — follow that pattern for user-facing feedback.

### Printing & PDF

- `App\Services\TicketPrinterService` wraps `mike42/escpos-php`. Constructor takes `(string $printerConfig, bool $isTestMode)`. **Test mode** (`isTestMode: true`) writes the ticket to `storage/app/tickets/*.txt` instead of a physical printer; production mode uses `WindowsPrintConnector` with the Windows printer name. `VentaController@store` currently hard-codes test mode — switch the commented lines there to go live.
- Invoices/reports use `barryvdh/laravel-dompdf` (`FacturaController`, `CierreCajaController` PDF exports).

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
