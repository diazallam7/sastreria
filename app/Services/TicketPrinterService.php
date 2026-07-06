<?php

namespace App\Services;

use Mike42\Escpos\PrintConnectors\FilePrintConnector;   // Para pruebas
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector; // Impresora térmica en red (ESC/POS sobre TCP)
use Mike42\Escpos\Printer;

class TicketPrinterService
{
    /**
     * @var Printer
     */
    protected $printer;

    /**
     * @var string IP/host de la impresora de red (modo producción) o ruta del archivo (modo prueba).
     */
    protected $printerConfig;

    /**
     * @var int Puerto TCP de la impresora (9100 = raw/JetDirect, estándar ESC/POS).
     */
    protected $printerPort;

    /**
     * @var bool Si es true, usa FilePrintConnector.
     */
    protected $isTestMode;

    public function __construct(string $printerConfig = '', bool $isTestMode = false, int $printerPort = 9100)
    {
        $this->printerConfig = $printerConfig;
        $this->isTestMode = $isTestMode;
        $this->printerPort = $printerPort;
        $this->connect();
    }

    /**
     * Establece la conexión con la impresora.
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function connect()
    {
        try {
            if ($this->isTestMode) {
                // Modo de prueba: imprime a un archivo
                $filePath = storage_path('app/tickets/ticket_venta_'.date('YmdHis').'_'.uniqid().'.txt');
                // Asegúrate de que el directorio exista
                if (! file_exists(dirname($filePath))) {
                    mkdir(dirname($filePath), 0777, true);
                }
                $connector = new FilePrintConnector($filePath);
            } else {
                // Modo de producción: impresora térmica en red (IP:puerto, protocolo ESC/POS raw)
                if (empty($this->printerConfig)) {
                    throw new \Exception('La IP de la impresora no está configurada para el modo de producción.');
                }
                $connector = new NetworkPrintConnector($this->printerConfig, $this->printerPort);
            }
            $this->printer = new Printer($connector);
        } catch (\Exception $e) {
            // Es crucial capturar y relanzar la excepción para que el controlador pueda manejarla
            throw new \Exception('No se pudo conectar a la impresora: '.$e->getMessage());
        }
    }

    /**
     * Imprime un ticket de venta.
     *
     * @param  array  $ventaData  Datos generales de la venta (id, fecha, total, cajero, cliente)
     * @param  array  $productos  Array de productos con 'nombre', 'cantidad', 'precio_u', 'subtotal'
     * @return bool True si la impresión fue exitosa.
     *
     * @throws \Exception Si ocurre un error durante la impresión.
     */
    public function printSaleTicket(array $ventaData, array $productos): bool
    {
        if (! $this->printer) {
            throw new \Exception('La impresora no está conectada.');
        }

        try {
            // --- Encabezado del Ticket ---
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            // Si tienes un logotipo, descomenta y ajusta la ruta
            // try {
            //     $logo = EscposImage::load(public_path('images/logo_ticket.png'), false);
            //     $this->printer->bitImage($logo);
            // } catch (\Exception $e) {
            //     // Maneja el error si el logo no se carga, pero no detengas la impresión del ticket
            //     Log::warning("No se pudo cargar el logo para el ticket: " . $e->getMessage());
            // }

            $this->printer->text("--- Sastreria Medina ---\n");
            $this->printer->text("Tu Direccion o Info de Contacto\n");
            $this->printer->text("RUC: 1234567-8\n");
            $this->printer->text("--------------------------------\n");

            $this->printer->setJustification(Printer::JUSTIFY_LEFT);
            $this->printer->text('Nro Ticket: '.($ventaData['id'] ?? 'N/A')."\n");
            $this->printer->text('Fecha: '.($ventaData['fecha'] ?? date('Y-m-d H:i:s'))."\n");
            $this->printer->text('Cajero: '.($ventaData['cajero'] ?? 'Desconocido')."\n");
            $this->printer->text('Cliente: '.($ventaData['cliente'] ?? 'Consumidor Final')."\n");
            $this->printer->text("--------------------------------\n");

            // --- Detalles de Productos ---
            // Formato: Nombre (18 chars) | Cant (5 chars) | Total (8 chars)
            $this->printer->text(sprintf("%-18s %5s %8s\n", 'Producto', 'Cant', 'Total'));
            $this->printer->text("--------------------------------\n");

            foreach ($productos as $producto) {
                $item = sprintf(
                    "%-18s %5s %8.2f\n", // Asegura que el subtotal tenga 2 decimales
                    substr($producto['nombre'], 0, 18), // Limita el nombre
                    $producto['cantidad'],
                    $producto['subtotal']
                );
                $this->printer->text($item);
            }

            $this->printer->text("--------------------------------\n");

            // --- Resumen de Venta ---
            $this->printer->setJustification(Printer::JUSTIFY_RIGHT);
            $this->printer->text(sprintf("Subtotal: Gs. %10.2f\n", $ventaData['precio_total'] ?? 0));
            $this->printer->text(sprintf("TOTAL:    Gs. %10.2f\n", $ventaData['precio_total'] ?? 0));
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->text("--------------------------------\n");
            $this->printer->text("¡Gracias por su compra!\n");
            $this->printer->text("Visítenos de nuevo!\n");
            $this->printer->text("--------------------------------\n");

            // --- Códigos de Barras/QR (Opcional) ---
            // Si quieres imprimir un QR con el ID de la venta
            // try {
            //     if (isset($ventaData['id'])) {
            //         $this->printer->qrCode((string)$ventaData['id'], Printer::QR_ECLEVEL_L, 8);
            //         $this->printer->text("ID: " . $ventaData['id'] . "\n");
            //     }
            // } catch (\Exception $e) {
            //     Log::warning("No se pudo generar el QR para el ticket: " . $e->getMessage());
            // }

            $this->printer->cut(); // Corta el papel

            return true; // Impresión exitosa

        } catch (\Exception $e) {
            // Si hay un error durante la impresión (ej. papel atascado, sin papel)
            throw new \Exception('Error al enviar datos a la impresora: '.$e->getMessage());
        } finally {
            // Siempre cierra la conexión a la impresora
            $this->close();
        }
    }

    /**
     * Cierra la conexión de la impresora.
     *
     * @return void
     */
    public function close()
    {
        if ($this->printer) {
            $this->printer->close();
            $this->printer = null;
        }
    }
}
