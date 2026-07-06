# Especificación — Códigos de barra (Sastrería Medina)

> Documento de diseño para implementar en una próxima sesión de Claude Code.
> **No contiene código**: describe schema, archivos, comportamiento y criterios de aceptación.
> Dominio en español, Laravel 11 + Livewire 3 + Tailwind. Sin `package.json` para PHP; JS vía Vite (`pnpm build`).

---

## 0. Decisiones ya tomadas (no reabrir)

| Tema | Decisión |
|------|----------|
| Granularidad en **alquiler** | **Por unidad física** (cada prenda = entidad con código único y estado). |
| Origen del código | **Mixto**: comprados usan su EAN de fábrica si lo traen; fabricados y stock de alquiler el sistema genera **Code128**. |
| Etiquetas | El sistema **genera PDF** de etiquetas Code128 (vía DomPDF). |
| Ventas | Código a nivel **SKU** = `producto_talle` (no por unidad). |
| Reservas | Siguen a nivel **SKU/talle** (se reserva "un talle M", la unidad física se asigna al entregar/convertir). |

---

## 1. Conceptos base

### 1.1 El lector es un teclado
Un lector de barras USB/BT actúa como teclado HID: escanea → "teclea" los caracteres del código y termina con **Enter**. No requiere driver ni SDK. Cualquier `<input>` enfocado recibe el texto. El **escaneo global** (Fase 3) se apoya en esto: un listener JS detecta la ráfaga de teclas y el Enter final.

### 1.2 Formato
- **Code128** para códigos internos (fabricados + alquiler): alfanumérico, compacto, estándar de SKU interno. Permite prefijos tipo `PRD-` / `ALQ-`.
- **EAN-13** para comprados que ya lo traen impreso de fábrica: se guarda tal cual (numérico, 13 dígitos).

### 1.3 Convención de códigos (CLAVE para el ruteo global)

| Origen | Formato | Ejemplo |
|--------|---------|---------|
| Venta SKU generado (fabricado, o comprado sin EAN) | `PRD-` + `producto_talle_id` con padding 7 | `PRD-0000123` |
| Venta comprado con EAN de fábrica | EAN crudo (solo dígitos) | `7791234567890` |
| Unidad de alquiler | `ALQ-` + `unidad_stock_id` con padding 7 | `ALQ-0000045` |

**Regla de desambiguación del escáner global:**
- Empieza con `PRD-` → módulo **Ventas**.
- Empieza con `ALQ-` → módulo **Alquiler**.
- Solo dígitos (EAN) → siempre **Ventas** (alquiler nunca usa EAN puro).

Padding a 7 dígitos es arbitrario pero fijo: mantenerlo consistente en generación y en el parser.

### 1.4 Librería
- Composer: **`picqer/php-barcode-generator`** (renderiza Code128 como PNG/SVG para incrustar en el PDF). Instalar con `composer require picqer/php-barcode-generator`.
- Escaneo: **sin librería** (JS propio en `resources/js/app.js`).

---

## 2. Modelo de datos actual (referencia)

Tablas relevantes hoy (migraciones consolidadas `2025_01_01_00000X_*`):

- `productos` (id, nombre, tipo[`comprado`|`fabricado`], precio_venta, precio_compra, fecha_compra, activo_para_venta, softDeletes).
- `producto_talles` (id, producto_id, talle, cantidad_total, cantidad_disponible, cantidad_vendida). UNIQUE(producto_id, talle). CHECK ≥ 0.
- `stock_alquiler` (id, codigo[UNIQUE], nombre, precio_alquiler, descripcion, softDeletes).
- `talle_stock` (id, stock_id, talle, cantidad_total, cantidad_disponible, cantidad_alquilada, cantidad_reservada). UNIQUE(stock_id, talle). CHECK ≥ 0.
- `alquiler_stock` (pivote: id, alquiler_id, stock_id, talle_id, cantidad).
- `ventas` / `detalle_ventas` (detalle: producto_talle_id nullable, snapshot nombre_producto/talle/precio).
- `reservas` / `reserva_stock` (pivote análogo con cantidad; decrementa `cantidad_reservada`).

Servicios (lógica de negocio, transaccional con locks):
- `VentaService` (crear/actualizar/anular; `aplicarItems` con `lockForUpdate`).
- `AlquilerService` (crear/actualizar/anular; `entregarPrendas`/`restaurarStock`).
- `DevolucionService` (`calcular`/`procesar`, camino único de devolución).
- `StockAlquilerService` (`guardar`/`sincronizarTalles`).
- `ReservaService`, `ReporteCajaService`.

Componentes Livewire con carrito/selector:
- `Ventas\Form`: props `items[]` (con `producto_talle_id`, nombre, talle, precio, cantidad), `productoSel`, `talleSel`, `cantidadSel`; método `agregarItem`. Escucha `#[On('cliente-creado')]`.
- `Alquileres\Form`: props `prendas[]` (con `stock_id`, `talle_id`, nombre, talle, precio, cantidad), `stockSel`, `talleSel`, `cantidadSel`; método `agregarPrenda`.

**Convención de migraciones del proyecto**: sin datos de producción → las columnas nuevas se agregan **editando la migración consolidada existente** y se aplica con `php artisan migrate:fresh --seed` (mismo patrón usado para `users.oculto`). No crear migraciones incrementales sueltas salvo indicación.

**Regla de oro (CLAUDE.md)**: toda mutación de stock va envuelta en transacción con `lockForUpdate`. Decrementar al crear, restaurar antes de re-aplicar en update, restaurar al borrar. Equivocarse corrompe inventario.

---

## 3. Fase 0 — Preparación

**Objetivo:** dejar la base común lista.

Tareas:
1. `composer require picqer/php-barcode-generator`.
2. Crear **`app/Services/BarcodeService.php`** con responsabilidades:
   - `generarCodigoVenta(int $productoTalleId): string` → `PRD-` + padding.
   - `generarCodigoUnidad(int $unidadId): string` → `ALQ-` + padding.
   - `parsear(string $codigo): array` → devuelve `['tipo' => 'venta'|'alquiler', 'ref_id' => int|null, 'raw' => string]` según prefijo/formato (EAN → `venta`, ref_id null, se resuelve por lookup de columna).
   - `pngBase64(string $codigo): string` → data-URI PNG del Code128 (para el `<img>` del PDF). Para EAN usar tipo EAN-13, para `PRD-/ALQ-` usar Code128.
3. Documentar en `CLAUDE.md` (sección "Códigos de barra") la convención de prefijos y que el lector es teclado.

**Criterio de aceptación:** `BarcodeService` unit-testeado (genera formato correcto; `parsear` clasifica bien `PRD-`, `ALQ-`, EAN).

---

## 4. Fase 1 — Ventas (nivel SKU)

**Objetivo:** cada `producto_talle` tiene un código de barras; escanear en el form de ventas agrega el ítem al carrito; se pueden imprimir etiquetas.

### 4.1 Schema
Editar migración `2025_01_01_000003_create_productos_tables.php`:
- `producto_talles`: agregar `codigo_barra` string, **UNIQUE**, nullable (se asigna post-insert porque `PRD-` depende del id).
  - Considerar índice UNIQUE que tolere NULL (MySQL permite múltiples NULL en UNIQUE).

### 4.2 Modelo
- `App\Models\ProductoTalle`: agregar `codigo_barra` a `$fillable`. Scope helper `porCodigo(string $codigo)` (busca por `codigo_barra`).

### 4.3 Generación del código
En el flujo de alta/edición de producto (`Productos\Form` + posible `ProductoService`):
- Al **crear** un `producto_talle`:
  - Si `producto.tipo === 'comprado'` y el usuario cargó un EAN manual → usar ese (validar unicidad, solo dígitos).
  - Si no → tras guardar (ya con id), asignar `codigo_barra = BarcodeService::generarCodigoVenta(id)` y guardar de nuevo (segundo save o update dentro de la misma transacción).
- UI de `Productos\Form`: por talle, input opcional **"Código / EAN"** (placeholder "se genera automático si lo dejás vacío"). Mostrar el código resultante en `Productos\Show` y en el index (o al menos en Show).

### 4.4 Escaneo en el form de ventas
- `Ventas\Form`: nuevo método `escanear(string $codigo)`:
  1. `BarcodeService::parsear` → si no es venta, ignorar/flash.
  2. Resolver `ProductoTalle` por `codigo_barra` (prefijo `PRD-` → también se puede resolver por id parseado; EAN → por columna).
  3. Validaciones: producto `activo_para_venta`, `cantidad_disponible > 0`.
  4. Si el ítem ya está en `items[]` → incrementar cantidad (respetando stock). Si no → agregarlo (reutilizar la lógica de `agregarItem`).
  5. Flash/toast de confirmación ("Agregado: {nombre} T:{talle}").
- Blade `ventas/form.blade.php`: input de escaneo **siempre enfocado** (un `<input>` dedicado, o capturar vía listener global de Fase 3). Para Fase 1 alcanza un input visible tipo "Escaneá o tipeá el código" que en Enter dispara `wire:keydown.enter` → `escanear($event.target.value)` y limpia.

### 4.5 Etiquetas PDF
- Ruta: `GET productos/{producto}/etiquetas` → `name('productos.etiquetas')`, `middleware('permission:ver-producto')` (o `editar-producto`).
- Controlador: nuevo `EtiquetaController@producto` (patrón DomPDF de `FacturaController`: `App::make('dompdf.wrapper')->loadView(...)->stream(...)`).
- Vista `resources/views/etiquetas/producto.blade.php`: grilla de etiquetas (una por talle, o repetida N veces según cantidad — decidir en implementación; recomendado: un input de "cantidad de etiquetas por talle" o 1 por talle). Cada etiqueta: nombre producto, talle, `<img src="{{ $pngBase64 }}">`, texto del código debajo.
- Botón "Etiquetas" en `Productos\Show` (target `_blank`).

### 4.6 Criterios de aceptación Fase 1
- Alta de producto fabricado genera `PRD-` único por talle.
- Alta de comprado con EAN respeta el EAN; sin EAN genera `PRD-`.
- Escanear un código válido en Ventas agrega/incrementa el ítem con validación de stock.
- Escanear código inexistente/inactivo/sin stock → mensaje claro, no rompe.
- PDF de etiquetas se genera y el código es legible por el lector (probar con lector real o app de escaneo).

### 4.7 Tests Fase 1
- `BarcodeService` (Fase 0) ya cubierto.
- Feature: crear producto asigna código; escanear agrega al carrito; escanear sin stock lanza validación; ruta de etiquetas responde 200 PDF.

---

## 5. Fase 2 — Alquiler (unidad física) — **el refactor grande**

**Objetivo:** cada prenda de alquiler es una **unidad física** con código único y estado; el alquiler referencia unidades concretas; devolución/anulación liberan esas unidades.

> ⚠️ Toca inventario de alquiler. Máxima disciplina con transacciones + locks + tests. Reservas quedan SKU-level (no cambian).

### 5.1 Modelo de datos

**Nueva tabla `unidad_stock`** (fuente de verdad de cada prenda física):
- `id`
- `talle_stock_id` FK → `talle_stock` (cascadeOnDelete)
- `codigo` string UNIQUE (`ALQ-...`, asignado post-insert por depender del id)
- `estado` string(20) index: `disponible` | `alquilada` | `baja`
- timestamps + softDeletes
- Enum nuevo: `App\Enums\EstadoUnidad`.

**Nuevo pivote `alquiler_unidad`** (reemplaza el modelo por-cantidad de alquiler):
- `id`, `alquiler_id` FK, `unidad_id` FK → `unidad_stock`
- `precio` decimal(14,0) — snapshot del `precio_alquiler` al momento
- timestamps
- UNIQUE(unidad_id) parcial en el sentido de "una unidad no puede estar en dos alquileres activos" (garantizar por lógica + estado, no solo constraint).

**`talle_stock`**: se mantienen los contadores (`cantidad_total`, `cantidad_disponible`, `cantidad_alquilada`, `cantidad_reservada`) como **denormalización/caché** para no reescribir reservas ni cierre de caja. Fuente de verdad de disponibilidad de alquiler = estados de `unidad_stock`; los contadores se sincronizan dentro de las mismas transacciones. Documentar esta dualidad.

**`alquiler_stock`**: decidir en implementación entre (a) eliminarlo y usar solo `alquiler_unidad`, o (b) conservarlo como agregado por talle. **Recomendado: eliminarlo** y derivar el agrupado por talle desde `alquiler_unidad` en las vistas. Esto obliga a actualizar `Alquiler::stockItems()` y todo lo que lo consuma (FacturaController, vistas de alquiler/devolución, ReporteCaja si aplica).

### 5.2 Generación de unidades
`StockAlquilerService::sincronizarTalles` cambia:
- Al **crear** un talle con `cantidad` = N → crear N filas `unidad_stock` (estado `disponible`, código `ALQ-` post-insert). Contadores del talle igual que hoy.
- Al **aumentar** cantidad (+d) → crear d unidades nuevas `disponible`.
- Al **disminuir** cantidad (−d) → marcar d unidades **`disponible`** como `baja` (softDelete o estado baja), nunca tocar unidades `alquilada`. Si no hay suficientes disponibles para dar de baja → validación (no permitir bajar por debajo de las alquiladas).
- Mantener CHECK ≥ 0 y sincronía de contadores.

### 5.3 Entrega / devolución con unidades
`AlquilerService::entregarPrendas`:
- Input desde el form sigue siendo `stock_id + talle_id + cantidad` (auto-asignación) **y/o** unidades específicas por escaneo.
- Auto-asignación: seleccionar N unidades `disponible` del talle con `lockForUpdate` (orden estable, p.ej. por id), setear `estado = alquilada`, crear filas `alquiler_unidad` con `precio` snapshot, decrementar contadores.
- Escaneo de unidad específica (`ALQ-`): asignar esa unidad exacta si está `disponible`.
- Validación: no alquilar unidades ya `alquilada`/`baja`.

`AlquilerService::restaurarStock` (update/anular): unidades del alquiler → `estado = disponible`, borrar filas `alquiler_unidad`, incrementar contadores.

`DevolucionService::procesar`: al restaurar stock, además de contadores, setear las unidades del alquiler a `disponible`. La `calcular`/multa no cambia. Mantener el **camino único** de devolución (guard anti doble-devolución con `lockForUpdate` ya existe).

### 5.4 Reservas (sin cambio de granularidad)
- Reserva sigue por talle/cantidad (`cantidad_reservada`). Al **convertir reserva → alquiler** (`ReservaController@convertirAAlquiler` / `ReservaService`), la asignación de unidades físicas ocurre ahí (mismo mecanismo de auto-asignación de 5.3): pasar de `cantidad_reservada` a unidades `alquilada`.

### 5.5 UI
- `StockAlquiler\Show`: listar las unidades del talle con su código y estado; botón para imprimir etiquetas de las unidades.
- `StockAlquiler\Form`: igual que hoy (cantidad por talle); la generación de unidades es automática.
- `Alquileres\Form`: permitir agregar por talle+cantidad (auto) y por escaneo de unidad (`ALQ-`). Mostrar en el carrito qué unidades concretas quedaron asignadas (o al menos el conteo; unidades exactas se fijan al guardar).
- `Alquileres\Show` y devolución: mostrar unidades concretas devueltas.
- Ajustar `FacturaController@alquiler` y `facturas/alquiler.blade.php` al nuevo origen (`alquiler_unidad` en vez de `alquiler_stock`).

### 5.6 Etiquetas PDF de unidades
- Ruta: `GET stock/alquiler/{item}/etiquetas` → `name('stock.alquiler.etiquetas')`, `middleware('permission:ver-stock-alquiler')`.
- `EtiquetaController@stockAlquiler`: una etiqueta **por unidad física** (código `ALQ-` distinto cada una), agrupadas por talle. Nombre prenda + talle + código + barcode PNG.

### 5.7 Criterios de aceptación Fase 2
- Crear stock con cantidades genera exactamente N unidades `disponible` por talle, con códigos únicos.
- Alquilar decrementa disponibles y marca unidades `alquilada`; contadores coherentes con estados.
- Devolver/anular/editar restaura unidades a `disponible` sin duplicar ni perder stock.
- Bajar cantidad nunca da de baja una unidad `alquilada`.
- Convertir reserva asigna unidades físicas.
- No es posible alquilar dos veces la misma unidad (concurrencia con locks).
- Factura de alquiler y devolución muestran unidades correctas.

### 5.8 Tests Fase 2 (críticos)
- Sincronización de unidades al crear/subir/bajar cantidad.
- Alquiler → devolución: contadores y estados vuelven al punto inicial (test de invariante).
- Doble devolución bloqueada (ya existe, revalidar con unidades).
- Concurrencia: dos alquileres compitiendo por la última unidad → uno falla.
- Reserva→alquiler asigna unidad.
- Actualizar los tests existentes de alquiler/devolución que asumían `alquiler_stock`/cantidad.

---

## 6. Fase 3 — Escaneo global

**Objetivo:** escanear en **cualquier** pantalla del sistema dispara la acción correcta sin foco manual en un input.

### 6.1 Detección (JS en `resources/js/app.js`)
- Listener global de `keydown` en `document`.
- Heurística de lector: acumula caracteres; si el **gap entre teclas < ~50ms** y termina con **Enter**, y el buffer tiene longitud plausible (≥ 4), se trata como **escaneo**; si el gap es humano (>100ms) se descarta el buffer.
- Ignorar cuando el foco está en un `<input>/<textarea>` de escritura libre **salvo** que sea el input de escaneo dedicado (para no romper búsquedas/campos). Definir excepción por atributo `data-scan-ignore` o por tag.
- Al detectar escaneo completo → llamar a un ruteador JS.

### 6.2 Ruteo (según convención §1.3)
- `PRD-` o EAN numérico:
  - Si la ruta actual es `ventas.create`/`ventas.edit` → `Livewire.dispatch('barcode-scanned', { codigo })` (el componente `Ventas\Form` lo maneja con `#[On('barcode-scanned')]` → `escanear`).
  - Si no → `wire:navigate` / redirect a `ventas.create?scan={codigo}`; `Ventas\Form::mount` lee `?scan=` (query, `#[Url]` o request) y auto-agrega.
- `ALQ-`:
  - Si estás en `alquileres.create`/`edit` → dispatch a `Alquileres\Form` (`#[On('barcode-scanned')]` → agrega unidad).
  - Si no → navegar a `alquileres.create?scan={codigo}`.
- Código no resoluble → toast de error global (usar el store/tooltip ya existente, o un toast nuevo).

### 6.3 Consideraciones
- El dispatch de Livewire desde JS: usar `window.Livewire.dispatch(...)`. Confirmar que el componente destino esté montado.
- Query param `?scan=`: consumir una sola vez en `mount` y limpiarlo para no re-agregar en refresh.
- `livewire:navigated`: re-evaluar la ruta actual para el ruteo (SPA).
- Recordar `pnpm build` + borrar `public/hot` tras tocar `app.js`.

### 6.4 Criterios de aceptación Fase 3
- Escanear `PRD-` desde el Dashboard abre Ventas con el ítem cargado.
- Escanear `PRD-` ya estando en Ventas lo agrega sin recargar.
- Escanear `ALQ-` desde cualquier lado lleva a Alquiler con la unidad.
- Tipear rápido a mano en un buscador no dispara falsos escaneos.
- Código inválido → toast, sin navegación.

### 6.5 Tests Fase 3
- Difícil E2E sin navegador; testear la parte server: `Ventas\Form` con `?scan=` agrega; `#[On('barcode-scanned')]` agrega. La heurística JS se valida manual.

---

## 7. Fase 4 (opcional) — Pulido

- Reimpresión de etiquetas individuales (una unidad / un talle).
- Búsqueda por código en los index (ventas, stock).
- Reporte "unidades de baja" / trazabilidad por unidad (historial de alquileres de una prenda física).
- Etiquetas con logo/estilo de la sastrería.
- Config de "cantidad de etiquetas por hoja" / tamaño.

---

## 8. Orden de ejecución sugerido

1. **Fase 0** (base + lib + BarcodeService + tests).
2. **Fase 1** (ventas SKU + etiquetas) — entrega valor sin tocar alquiler.
3. **Fase 2** (unidad física en alquiler) — el refactor pesado; hacer con tests de invariante primero.
4. **Fase 3** (escaneo global).
5. **Fase 4** (pulido) según necesidad.

Cada fase: implementar → `php artisan test` (MySQL `sastreriaheidi_test`) → si tocó JS, `pnpm build` + `rm -f public/hot` → `vendor/bin/pint`.

---

## 9. Riesgos / notas

- **Inventario de alquiler (Fase 2)** es lo más delicado: la dualidad contadores↔unidades debe mantenerse siempre sincronizada dentro de transacciones con `lockForUpdate`. Escribir primero un test de invariante (alquilar→devolver deja todo igual) y correrlo tras cada cambio.
- **UNIQUE con NULL**: `codigo_barra` nullable en `producto_talles` permite varios NULL en MySQL; asegurar que todos terminen con código tras el alta.
- **EAN vs generado**: validar que un EAN cargado a mano no colisione con el patrón `PRD-`/`ALQ-` (los EAN son solo dígitos, no chocan).
- **DomPDF + barcode**: incrustar el barcode como PNG base64 `<img>` (DomPDF renderiza PNG data-URI de forma confiable; evitar SVG si da problemas).
- **Migraciones**: sin datos productivos → editar migración consolidada + `migrate:fresh --seed`. Si en el futuro hubiera datos reales, esto cambia (habría que migrar incremental y backfillear códigos/unidades).
- **Reservas**: intencionalmente quedan SKU-level; la unidad física se materializa recién al entregar/convertir.
- Mantener FontAwesome versión **CSS**, Livewire v3, y responder builds con `pnpm` (no `pnpm dev`).

---

## 10. Checklist rápido por fase

**Fase 0:** `composer require picqer/php-barcode-generator` · `BarcodeService` · tests · doc CLAUDE.md.

**Fase 1:** col `producto_talles.codigo_barra` · generación en alta producto · `Ventas\Form::escanear` + input · `EtiquetaController@producto` + ruta + vista · botón en `Productos\Show` · tests · `migrate:fresh --seed`.

**Fase 2:** enum `EstadoUnidad` · tabla `unidad_stock` · pivote `alquiler_unidad` (quitar `alquiler_stock`) · refactor `StockAlquilerService`/`AlquilerService`/`DevolucionService`/`ReservaService` · `Alquiler::stockItems`→unidades · UI stock/alquiler/devolución · `EtiquetaController@stockAlquiler` + ruta + vista · FacturaController alquiler · tests de invariante · `migrate:fresh --seed`.

**Fase 3:** listener en `app.js` (heurística ráfaga+Enter) · ruteo por prefijo · `?scan=` en Ventas/Alquileres Form · `#[On('barcode-scanned')]` · toast error · `pnpm build` + `rm -f public/hot`.
