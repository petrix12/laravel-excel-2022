# Aprende a generar reportes con Laravel-Excel
+ URL: https://codersfree.com/cursos/aprende-a-generar-reportes-con-laravel-excel
+ Instructor: Víctor Arana Flores

## Cración de repositorio en GitHub
1. Crear proyecto en la página de [GitHub](https://github.com) con el nombre: **laravel-excel-2022**.
    + **Description**: Proyecto para seguir el curso de "Aprende a generar reportes con Laravel-Excel", creado por Víctor Arana Flores en Coders Free.
    + **Public**.
2. En la ubicación raíz del proyecto en la terminal de la máquina local:
    + $ git init
    + $ git add .
    + $ git commit -m "Antes de iniciar"
    + $ git branch -M main
    + $ git remote add origin https://github.com/petrix12/laravel-excel-2022.git
    + $ git push -u origin main


## Introducción
### 1. Instalar proyecto
1. Crear proyecto:
    + $ laravel new laravelexcel2022 --jet
    + Which Jetstream stack do you prefer?
        + [0] livewire
    + Will your application use teams? (yes/no) [no]: no
2. Crear base de datos **laravelexcel2022** en MySQL.
    + **Nota**: seleccionar juego de caracteres: utf8_general_ci
3. Ejecutar migraciones:
    + $ php artisan migrate
4. Ejecutar proyecto:
    + $ npm run serve

### 2. Crear Virtualhost
+ Como generar un dominio local en windows xampp: https://codersfree.com/posts/como-generar-un-dominio-local-en-windows-xampp

### 3. Instalar Laravel Excel
+ https://docs.laravel-excel.com/3.1/getting-started/installation.html
1. Habilitar extensión de PHP **php_gd2**:
    + Modificar php.ini (C:\laragon\bin\php\php-8.1.5\php.ini o C:\xampp\php\php.ini):
    ```ini
    ≡
    extension=fileinfo
    extension=gd
    ;extension=gettext
    ≡
    ```
    + **Nota**: descomentar la instrucción **extension=gd** y reiniciar el servidor web.
2. Instalar dependencia **maatwebsite/excel**:
    + $ composer require maatwebsite/excel
    + **Nota**: en caso de presentar fallas en la instalación, ejeuctar:
    + $ composer require psr/simple-cache:^2.0 maatwebsite/excel


## Estructura del proyecto
### 4. Crear migraciones
1. Crear modelo **Invoice** junto con su migración, factory y controlador:
    + $ php artisan make:model Invoice -mfc
2. Modificar migración **Invoice** (database\migrations\2022_09_24_161105_create_invoices_table.php):
    ```php
    ≡
    Schema::create('invoices', function (Blueprint $table) {
        $table->id();
        $table->string('serie');
        $table->integer('correlative');
        $table->float('base');
        $table->float('igv');
        $table->float('total');
        $table->foreignId('user_id')->constrained();
        $table->timestamps();
    ≡
    });
    ```
3. Generar migración:
    + $ php artisan migrate

### 5. Agregar datos de prueba
1. Definir factory para **InvoiceFactory** (database\factories\InvoiceFactory.php):
    ```php
    ≡
    use App\Models\User;
    use Illuminate\Database\Eloquent\Factories\Factory;
    ≡
    class InvoiceFactory extends Factory
    {
        ≡
        public function definition()
        {
            $base = $this->faker->randomFloat(2, 100, 1000);
            $igv = $base * 0.18;
            $total = $base + $igv;

            return [
                'serie' => $this->faker->randomElement(['F001', 'B001']),
                'base' => $base,
                'igv' => $igv,
                'total' => $total,
                'user_id' => User::all()->random()->id
            ];
        }
    }
    ```
2. Crear observer **InvoiceObserver** para la tabla **invoices**:
    + $ php artisan make:observer InvoiceObserver
3. Definir el observer **InvoiceObserver** (app\Observers\InvoiceObserver.php):
    ```php
    <?php

    namespace App\Observers;

    use App\Models\Invoice;

    class InvoiceObserver
    {
        public function creating(Invoice $invoice){
            $invoice->correlative = Invoice::where('serie', $invoice->serie)->count() + 1;
        }
    }
    ```
4. Registrar observer **InvoiceObserver** en el provider **EventServiceProvider** (app\Providers\EventServiceProvider.php):
    ```php
    ≡
    public function boot()
    {
        \App\Models\Invoice::observe(\App\Observers\InvoiceObserver::class);
    }
    ≡
    ```
5. Configurar seeder **DatabaseSeeder** (database\seeders\DatabaseSeeder.php):
    ```php
    ≡
    public function run()
    {
        \App\Models\User::create([
            'name' => 'Pedro Bazó',
            'email' => 'bazo.pedro@gmail.com',
            'password' => bcrypt('12345678')
        ]);
        \App\Models\User::factory(10)->create();
        \App\Models\Invoice::factory(100)->create();
    }
    ≡
    ```
6. Ejecutar los seeder:
    + $ php artisan db:seed

### 6. Agregar relaciones
1. Establecer ralaciones en el modelo **User** (app\Models\User.php):
    ```php
    class User extends Authenticatable
    {
        ≡
        // Relación 1:n users - invoices
        public function invoices(){
            return $this->hasMany(Invoice::class);
        }
    }
    ```
2. Establecer ralaciones y habilitar asignación masiva en el modelo **Invoice** (app\Models\Invoice.php):
    ```php
    class Invoice extends Model
    {
        ≡
        protected $fillable = [
            'serie',
            'correlative',
            'base',
            'igv',
            'total',
            'user_id'
        ];

        // Relación 1:n inversa users - invoices
        public function user(){
            return $this->belongsTo(User::class);
        }
    }
    ```

### 7. Crear rutas
1. Modificar el archiv de rutas **web** (routes\web.php):
    ```php
    ≡
    Route::get('/invoice/export', [\App\Http\Controllers\InvoiceController::class, 'export'])->name('invoices.export');
    Route::get('/invoice/import', [\App\Http\Controllers\InvoiceController::class, 'import'])->name('invoices.import');
    ```
2. Programar el controlador **InvoiceController** (app\Http\Controllers\InvoiceController.php):
    ```php
    ≡
    class InvoiceController extends Controller
    {
        public function export(){
            return view('invoices.export');
        }

        public function import(){
            return view('invoices.import');
        }
    }
    ≡
    ```
3. Diseñar vista **import** (resources\views\invoices\import.blade.php):
    ```php
    <x-app-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Import Invoice') }}
            </h2>
        </x-slot>
    </x-app-layout>
    ```
4. Diseñar vista **export** (resources\views\invoices\export.blade.php):
    ```php
    <x-app-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Export Invoice') }}
            </h2>
        </x-slot>
    </x-app-layout>
    ```
5. Modifcar vista **resources\views\navigation-menu.blade.php**:
    ```php
    ≡
    <!-- Navigation Links -->
    <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
        <x-jet-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
            {{ __('Dashboard') }}
        </x-jet-nav-link>

        <x-jet-nav-link href="{{ route('invoices.export') }}" :active="request()->routeIs('invoices.export')">
            {{ __('Export') }}
        </x-jet-nav-link>

        <x-jet-nav-link href="{{ route('invoices.import') }}" :active="request()->routeIs('invoices.import')">
            {{ __('Import') }}
        </x-jet-nav-link>
    </div>
    ≡
    ```

## Invoices
### 8. Mostrar invoices
1. Crear componente Livewire **filter-invoices**:
    + $ php artisan make:livewire filter-invoices
2. Modificar vista **export** (resources\views\invoices\export.blade.php):
    ```PHP
    <x-app-layout>
        ≡

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                @livewire('filter-invoices')
            </div>
        </div>
    </x-app-layout>
    ```
3. Programar el controlador del componente **filter-invoices** (app\Http\Livewire\FilterInvoices.php):
    ```php
    <?php

    namespace App\Http\Livewire;

    use App\Models\Invoice;
    use Livewire\Component;
    use Livewire\WithPagination;

    class FilterInvoices extends Component
    {
        use WithPagination;
        
        public function render()
        {
            $invoices = Invoice::paginate(10);
            return view('livewire.filter-invoices', compact('invoices'));
        }
    }
    ```
4. Diseñar la vista del componente **filter-invoices** (resources\views\livewire\filter-invoices.blade.php):
    ```php
    <div>
        <div class="overflow-x-auto relative">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="py-3 px-6">
                            ID
                        </th>
                        <th scope="col" class="py-3 px-6">
                            Serie
                        </th>
                        <th scope="col" class="py-3 px-6">
                            Correlativo
                        </th>
                        <th scope="col" class="py-3 px-6">
                            Base
                        </th>
                        <th scope="col" class="py-3 px-6">
                            IGV
                        </th>
                        <th scope="col" class="py-3 px-6">
                            Total
                        </th>
                        <th scope="col" class="py-3 px-6">
                            Fecha
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoices as $invoice)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <th scope="row" class="py-4 px-6 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ $invoice->id }}
                            </th>
                            <td class="py-4 px-6">
                                {{ $invoice->serie }}
                            </td>
                            <td class="py-4 px-6">
                                {{ $invoice->correlative }}
                            </td>
                            <td class="py-4 px-6">
                                $ {{ $invoice->base }}
                            </td>
                            <td class="py-4 px-6">
                                $ {{ $invoice->igv }}
                            </td>
                            <td class="py-4 px-6">
                                $ {{ $invoice->total }}
                            </td>
                            <td class="py-4 px-6">
                                {{ $invoice->created_at->format('d-m-y') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $invoices->links() }}
        </div>
    </div>
    ```
+ **Nota**: en caso de que no se muestren los estilos correctamente, ejecutar:
    + $ npm run dev
  
### 9. Agregar filtros
1. Modificar vista del componente **filter-invoices** (resources\views\livewire\filter-invoices.blade.php):
    ```php
    <div>
        {{-- Filtros --}}
        <div class="bg-white rounded p-8 shadow mb-6">
            <h2 class="text-2xl font-semibold mb-4">Generar reportes</h2>

            <div class="mb-4">
                Serie:
                <select wire:model="filters.serie" name="serie" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm w-32">
                    <option value="">Todos</option>
                    <option value="F001">F001</option>
                    <option value="B001">B001</option>
                </select>

                <div class="flex space-x-4 my-4">
                    <div>
                        Desde el Nº:
                        <x-jet-input wire:model="filters.fromNumber" type="text" class="w-20" />
                    </div>
                    <div>
                        Hasta el Nº:
                        <x-jet-input wire:model="filters.toNumber" type="text" class="w-20" />
                    </div>
                </div>

                <div class="flex space-x-4 mb-4">
                    <div>
                        Desde fecha:
                        <x-jet-input wire:model="filters.fromDate" type="date" class="w-36" />
                    </div>
                    <div>
                        Hasta fecha:
                        <x-jet-input wire:model="filters.toDate" type="date" class="w-36" />
                    </div>
                </div>
            </div>
            <x-jet-button>
                Generar reporte
            </x-jet-button>
        </div>

        {{-- Tabla --}}
        <div class="overflow-x-auto relative">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                ≡
            </table>
        </div>
        ≡
    </div>
    ≡
    ```
2. Publicar componentes Jetstream:
    + $ php artisan vendor:publish --tag=jetstream-views
    + **Nota**: los componentes Jetstream se ubicaran en **resources\views\vendor\jetstream**.
    + **Documentation**: https://jetstream.laravel.com/2.x/installation.html
3. Modificar el controlador del componente **filter-invoices** (app\Http\Livewire\FilterInvoices.php):
    ```php
    ≡
    class FilterInvoices extends Component
    {
        use WithPagination;

        public $filters = [
            'serie' => '',
            'fromNumber' => '',
            'toNumber' => '',
            'fromDate' => '',
            'toDate' => ''
        ];
        ≡
    }
    ```

### 10. Query Scopes
1. Modificar el modelo **Invoice** (app\Models\Invoice.php):
    ```php
    ≡
    public function render()
    {
        $invoices = Invoice::filter($this->filters)->paginate(10);
        return view('livewire.filter-invoices', compact('invoices'));
    }
    ≡
    ```
2. Modificar el controlador del componente **filter-invoices** (app\Http\Livewire\FilterInvoices.php):
    ```php
    ≡
    class Invoice extends Model
    {
        ≡
        // Query Scopes
        public function scopeFilter($query, $filters){
            $query->when($filters['serie'] ?? null, function($query, $serie){
                $query->where('serie', $serie);
            })->when($filters['fromNumber'] ?? null, function($query, $fromNumber){
                $query->where('correlative', '>=', $fromNumber);
            })->when($filters['toNumber'] ?? null, function($query, $toNumber){
                $query->where('correlative', '<=', $toNumber);
            })->when($filters['fromDate'] ?? null, function($query, $fromDate){
                $query->where('created_at', '>=', $fromDate);
            })->when($filters['toDate'] ?? null, function($query, $toDate){
                $query->where('created_at', '<=', $toDate);
            });
        }
        ≡
    }
    ```

## Exportación
### 11. Crear archivo de exportación
1. Modificar el controlador del componente **filter-invoices** (app\Http\Livewire\FilterInvoices.php):
    ```php
    ≡
    use App\Exports\InvoiceExport;
    use App\Models\Invoice;
    use Livewire\Component;
    use Livewire\WithPagination;
    use Maatwebsite\Excel\Facades\Excel;

    class FilterInvoices extends Component
    {
       ≡
        public function generateReport(){
            return Excel::download(new InvoiceExport, 'invoice.xlsx');
        }
        ≡
    }
    ```
2. Modificar vista del componente **filter-invoices** (resources\views\livewire\filter-invoices.blade.php):
    ```php
    ≡
    <div>
        {{-- Filtros --}}
        <div class="bg-white rounded p-8 shadow mb-6">
            ≡
            <x-jet-button wire:click="generateReport">
                Generar reporte
            </x-jet-button>
        </div>
        ≡
    </div>
    ```
3. Crear export **InvoiceExport** del modelo **Invoice**:
    + $ php artisan make:export InvoiceExport --model=Invoice
    + **Nota**: para indicar los datos del modelo a exportar, editar **app\Exports\InvoiceExport.php**.

### 12. Indicar la celda de inicio
1. Personalizar el export **InvoiceExport** (app\Exports\InvoiceExport.php):
    ```php
    <?php

    namespace App\Exports;

    use App\Models\Invoice;
    use Maatwebsite\Excel\Concerns\FromCollection;
    use Maatwebsite\Excel\Concerns\WithCustomStartCell;

    class InvoiceExport implements FromCollection, WithCustomStartCell
    {
        /**
        * @return \Illuminate\Support\Collection
        */
        public function collection()
        {
            return Invoice::all();
        }

        public function startCell(): string
        {
            return 'A10';
        }
    }
    ```

### 13. Exportar en otros formatos
1. Modificar el controlador del componente **filter-invoices** (app\Http\Livewire\FilterInvoices.php):
    ```php
    ≡
    public function generateReport(){
        return Excel::download(new InvoiceExport, 'invoice.csv', \Maatwebsite\Excel\Excel::CSV);
    }
    ≡
    ```

### 14. Exportables
1. Modificar el export **InvoiceExport** (app\Exports\InvoiceExport.php):
    ```php
    ≡
    use App\Models\Invoice;
    use Illuminate\Contracts\Support\Responsable;
    use Maatwebsite\Excel\Concerns\FromCollection;
    use Maatwebsite\Excel\Concerns\Exportable;
    use Maatwebsite\Excel\Concerns\WithCustomStartCell;
    use Maatwebsite\Excel\Excel;

    class InvoiceExport implements FromCollection, WithCustomStartCell, Responsable
    {
        use Exportable;

        private $fileName = 'invoice.xlsx';
        private $writerType = Excel::XLSX;

        /**
        * @return \Illuminate\Support\Collection
        */
        ≡
    }
    ```
2. Modificar el controlador del componente **filter-invoices** (app\Http\Livewire\FilterInvoices.php):
    ```php
    ≡
    public function generateReport(){
        return new InvoiceExport();
    }
    ≡
    ```

### 15. Pasar parametros
1. Modificar el controlador del componente **filter-invoices** (app\Http\Livewire\FilterInvoices.php):
    ```php
    ≡
    public function generateReport(){
        return new InvoiceExport($this->filters);
    }
    ≡
    ```
2. Modificar el export **InvoiceExport** (app\Exports\InvoiceExport.php):
    ```php
    ≡
    class InvoiceExport implements FromCollection, WithCustomStartCell, Responsable
    {
        use Exportable;

        private $filters;
        private $fileName = 'invoice.xlsx';
        private $writerType = Excel::XLSX;

        public function __construct($filters){
            $this->filters = $filters;
        }

        /**
        * @return \Illuminate\Support\Collection
        */
        public function collection()
        {
            return Invoice::filter($this->filters)->get();
        }
        ≡
    }
    ```


## Estilos
### 16. Dar formato a las fechas
1. Modificar el export **InvoiceExport** (app\Exports\InvoiceExport.php):
    ```php
    ≡
    use App\Models\Invoice;
    use Illuminate\Contracts\Support\Responsable;
    use Maatwebsite\Excel\Concerns\FromCollection;
    use Maatwebsite\Excel\Concerns\Exportable;
    use Maatwebsite\Excel\Concerns\WithCustomStartCell;
    use Maatwebsite\Excel\Concerns\WithColumnFormatting;
    use Maatwebsite\Excel\Concerns\WithMapping;
    use Maatwebsite\Excel\Excel;
    use PhpOffice\PhpSpreadsheet\Shared\Date;

    class InvoiceExport implements FromCollection, WithCustomStartCell, Responsable, WithMapping, WithColumnFormatting
    {
        ≡
        public function startCell(): string {
            return 'A10';
        }

        public function map($invoice): array {
            return [
                $invoice->serie,
                $invoice->correlative,
                $invoice->base,
                $invoice->igv,
                $invoice->total,
                $invoice->user->name,
                Date::dateTimeToExcel($invoice->created_at)
            ];
        }

        public function columnFormats(): array {
            return [
                'G' => 'dd/mm/yyyy'
            ];
        }
    }
    ```

### 17. Agregar cabeceras
1. Modificar el export **InvoiceExport** (app\Exports\InvoiceExport.php):
    ```php
    ≡
    use Maatwebsite\Excel\Concerns\WithHeadings;
    ≡
    class InvoiceExport implements FromCollection, WithCustomStartCell, Responsable, WithMapping, WithColumnFormatting, WithHeadings
    {
        ≡
        public function startCell(): string {
            return 'A10';
        }

        public function headings(): array {
            return [
                'Serie',
                'Correlativo',
                'Base',
                'IGV',
                'Total',
                'Usuario',
                'Fecha'
            ];
        }
        ≡
    }
    ```

### 18. Cambiar ancho de columnas
1. Modificar el export **InvoiceExport** (app\Exports\InvoiceExport.php):
    ```php
    ≡
    /* use Maatwebsite\Excel\Concerns\ShouldAutoSize; */    // Esta interfaz sirve cuando queremos establecer un ancho automático
    ≡
    use Maatwebsite\Excel\Concerns\WithColumnWidths;
    ≡
    class InvoiceExport implements FromCollection, WithCustomStartCell, Responsable,
    WithMapping, WithColumnFormatting, WithHeadings /* , ShouldAutoSize */, WithColumnWidths
    {  
        ≡
        public function columnWidths(): array {
            return [
                'A' => 10,
                'B' => 10,
                'C' => 10,
                'D' => 10,
                'E' => 10,
                'F' => 30,
                'G' => 15
            ];
        }
    }
    ```

### 19. Agregar logotipo
1. Unbicar un logo cualquiera en **public\img\logos\logo.png**.
2. Modificar el export **InvoiceExport** (app\Exports\InvoiceExport.php):
    ```php
    ≡
    use Maatwebsite\Excel\Concerns\WithDrawings;
    ≡
    class InvoiceExport implements FromCollection, WithCustomStartCell, Responsable,
    WithMapping, WithColumnFormatting, WithHeadings /* , ShouldAutoSize */, WithColumnWidths,
    WithDrawings
    {
        ≡
        public function drawings() {
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setName('Logotipo');
            $drawing->setDescription('Logotipo de Soluciones++');
            $drawing->setPath(public_path('img\logos\logo.png'));
            $drawing->setHeight(90);
            $drawing->setCoordinates('B3');

            return $drawing;
        }
    }
    ```

### 20. Cambiar nombre de la hoja
1. Modificar el export **InvoiceExport** (app\Exports\InvoiceExport.php):
    ```php
    ≡
    use Maatwebsite\Excel\Concerns\WithStyles;
    ≡
    use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

    class InvoiceExport implements FromCollection, WithCustomStartCell, Responsable,
    WithMapping, WithColumnFormatting, WithHeadings /* , ShouldAutoSize */, WithColumnWidths,
    WithDrawings, WithStyles
    {
        ≡
        public function styles(Worksheet $sheet){
            // Nombrar hoja
            $sheet->setTitle('Invoices');
            // Unir celdas
            $sheet->mergeCells('B8:F8');
            // Introducir valor
            $sheet->setCellValue('B8', 'Soluciones++');
            // Introducir formula
            $sheet->setCellValue('B9', '=7+12');
        }
    }
    ```

### 21. Cambiar fuentes
1. Modificar el export **InvoiceExport** (app\Exports\InvoiceExport.php):
    ```php
    ≡
    public function styles(Worksheet $sheet){
        // Nombrar hoja
        $sheet->setTitle('Invoices');
        // Unir celdas
        $sheet->mergeCells('B8:F8');
        // Introducir valor
        $sheet->setCellValue('B8', 'Soluciones++');
        // Introducir formula
        $sheet->setCellValue('B9', '=7+12');

        // Aplicar estilos a un rango
        $sheet->getStyle('A10:G10')->applyFromArray([
            'font' => [
                'bold' => true,
                'name' => 'Arial'
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center'
            ],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => [
                    'argb' => 'C5D9F1',
                ]
            ]
        ]);

    }
    ≡
    ```

### 22. Aplicar bordes
+ https://developer.mozilla.org/es/docs/Web/CSS/border-style#:~:text=La%20propiedad%20border%2Dstyle%20CSS,del%20borde%20de%20un%20elemento.
1. Modificar el export **InvoiceExport** (app\Exports\InvoiceExport.php):
    ```php
    ≡
    public function styles(Worksheet $sheet){
        // Nombrar hoja
        $sheet->setTitle('Invoices');
        // Unir celdas
        $sheet->mergeCells('B8:F8');
        // Introducir valor
        $sheet->setCellValue('B8', 'Soluciones++');
        // Introducir formula
        $sheet->setCellValue('B9', '=7+12');

        // Aplicar estilos a un rango
        /* $sheet->getStyle('A10:G10')->applyFromArray([
            'font' => [
                'bold' => true,
                'name' => 'Arial'
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center'
            ],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => [
                    'argb' => 'C5D9F1',
                ]
            ]
        ]);

        // Aplicar bordes a una tabla
        $sheet->getStyle('A10:G' . $sheet->getHighestRow())->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => 'thin'
                    //'borderStyle' => 'dotted'
                ]
            ]
        ]);

        // Dejar seleccionada una celda determinada
        $sheet->getStyle('A11')->applyFromArray([
        ]); */

        // Otra forma de aplicar estilos y bordes
        return [
            'A10:G10' => [
                'font' => [
                    'bold' => true,
                    'name' => 'Arial'
                ],
                'alignment' => [
                    'horizontal' => 'center',
                    'vertical' => 'center'
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => [
                        'argb' => 'C5D9F1',
                    ]
                ]
            ],
            'A10:G' . $sheet->getHighestRow() => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => 'thin'
                        //'borderStyle' => 'dotted'
                    ]
                ]
            ],
            'A11' => []
        ];
    }
    ≡
    ```


## Importación
### 23. Crear formulario de importación
1. Modificar la vista **import** (resources\views\invoices\import.blade.php):
    ```php
    <x-app-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Import Invoice') }}
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <form
                    action="{{ route('invoices.importStore') }}"
                    method="POST"
                    class="bg-white rounded p-8 shadow"
                    enctype="multipart/form-data"
                >
                    @csrf
                    <x-jet-validation-errors class="mb-4" />
                    <div>
                        <h2 class="text-2xl font-semibold mb-4">Por favor seleccione el archivo que quiere importar</h2>
                        <input type="file" name="file" accept=".csv, .xlsx">
                    </div>
                    <x-jet-button class="mt-4">
                        Importar archivo
                    </x-jet-button>
                </form>
            </div>
        </div>
    </x-app-layout>
    ```
2. Modificar el archivo de ruta **web** (routes\web.php):
    ```php
    ≡
    Route::post('/invoice/import', [\App\Http\Controllers\InvoiceController::class, 'importStore'])->name('invoices.importStore');
    ```
3. Modificar el controlador **InvoiceController** (app\Http\Controllers\InvoiceController.php):
    ```php
    ≡
    class InvoiceController extends Controller
    {
        ≡
        public function importStore(Request $request){
            $request->validate([
                'file' => 'required|mimes:csv,xlsx'
            ]);
            return "En importStore";
        }
    }
    ```

### 24. Crear archivo de importación
1. Crear import **InvoiceImport** del modelo **Invoice**:
    + $ php artisan make:import InvoiceImport --model=Invoice
    + **Nota**: para programar la importación, editar **app\Imports\InvoiceImport.php**.
2. Programar el import **InvoiceImport** (app\Imports\InvoiceImport.php):
    ```php
    <?php

    namespace App\Imports;

    use App\Models\Invoice;
    use Maatwebsite\Excel\Concerns\ToModel;

    class InvoiceImport implements ToModel
    {
        /**
        * @param array $row
        *
        * @return \Illuminate\Database\Eloquent\Model|null
        */
        public function model(array $row)
        {
            return new Invoice([
                'serie' => $row[0],
                'base' => $row[1],
                'igv' => $row[2],
                'total' => $row[3],
                'user_id' => 1
            ]);
        }
    }
    ```
3. Modificar el controlador **InvoiceController** (app\Http\Controllers\InvoiceController.php):
    ```php
    ≡
    <?php
    ≡
    use App\Imports\InvoiceImport;
    use Illuminate\Http\Request;
    use Maatwebsite\Excel\Facades\Excel;

    class InvoiceController extends Controller
    {
        ≡
        public function importStore(Request $request){
            $request->validate([
                'file' => 'required|mimes:csv,xlsx'
            ]);

            $file = $request->file('file');

            // Obtenemos la importación en un objeto JSON.
            // $ImportJson = Excel::toCollection(new InvoiceImport, $file);

            Excel::import(new InvoiceImport, $file);
            return "Se importó el archivo";
        }
    }
    ```

### 25. Importar fechas
1. Modificar el import **InvoiceImport** (app\Imports\InvoiceImport.php):
    ```php
    <?php

    namespace App\Imports;

    use App\Models\Invoice;
    use Carbon\Carbon;
    use Maatwebsite\Excel\Concerns\ToModel;
    use PhpOffice\PhpSpreadsheet\Shared\Date;

    class InvoiceImport implements ToModel
    {
        /**
        * @param array $row
        *
        * @return \Illuminate\Database\Eloquent\Model|null
        */
        public function model(array $row)
        {
            return new Invoice([
                'serie' => $row[0],
                'base' => $row[1],
                'igv' => $row[2],
                'total' => $row[3],
                'user_id' => 1,
                'created_at' => Carbon::instance(Date::excelToDateTimeObject($row[4]))
                //'created_at' => Carbon::createFromFormat('d/m/Y', $row[4])
            ]);
        }
    }
    ```
2. Modificar el modelo **Invoice** (app\Models\Invoice.php):
    ```php
    ≡
    protected $fillable = [
        'serie',
        'correlative',
        'base',
        'igv',
        'total',
        'user_id',
        'created_at'
    ];
    ≡
    ```

### 26. Colecciones
1. Modificar el import **InvoiceImport** (app\Imports\InvoiceImport.php):
    ```php
    <?php

    namespace App\Imports;

    use App\Models\Invoice;
    use Carbon\Carbon;
    use Maatwebsite\Excel\Concerns\ToCollection;
    //use Maatwebsite\Excel\Concerns\ToModel;
    use PhpOffice\PhpSpreadsheet\Shared\Date;

    class InvoiceImport implements /* ToModel */ ToCollection
    {
        public function collection($rows)
        {
            foreach($rows as $row){
                $invoice = Invoice::create([
                    'serie' => $row[0],
                    'base' => $row[1],
                    'igv' => $row[2],
                    'total' => $row[3],
                    'user_id' => 1,
                    'created_at' => Carbon::instance(Date::excelToDateTimeObject($row[4]))
                    //'created_at' => Carbon::createFromFormat('d/m/Y', $row[4])
                ]);

                /* Compras::create([
                    'invoice_id' => $invoice->id;
                ]); */
            }
        }
    }
    ```

### 27. Importar CSV
1. Modificar archivo de variables de entorno **.env**:
    ```env
    ≡
    FILESYSTEM_DISK=public
    ≡
    ```
2. Modificar archivo de rutas **web** (routes\web.php):
    ```php
    ≡
    use App\Imports\InvoiceImport;
    use Illuminate\Support\Facades\Route;
    use Maatwebsite\Excel\Facades\Excel;
    ≡
    Route::get('prueba', function (){
        return Excel::toCollection(new InvoiceImport, 'csv/invoices.csv');
    });
    ```
3. Modificar el import **InvoiceImport** (app\Imports\InvoiceImport.php):
    ```php
    <?php

    namespace App\Imports;

    use App\Models\Invoice;
    use Carbon\Carbon;
    use Maatwebsite\Excel\Concerns\ToCollection;
    //use Maatwebsite\Excel\Concerns\ToModel;
    use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
    use Maatwebsite\Excel\Concerns\WithGroupedHeadingRow;
    use PhpOffice\PhpSpreadsheet\Shared\Date;

    class InvoiceImport implements /* ToModel */ ToCollection, WithGroupedHeadingRow, WithCustomCsvSettings
    {
        public function collection($rows)
        {
            foreach($rows as $row){
                Invoice::create([
                    'serie' => $row[0],
                    'base' => $row[1],
                    'igv' => $row[2],
                    'total' => $row[3],
                    'user_id' => 1
                ]);
            }
        }

        // Definir delimitador y juego de caracteres de los archivos csv
        public function getCsvSettings(): array
        {
            return [
                'input_encoding' => 'UTF-8',
                'delimiter' => ';'
            ];
        }
    }
    ```
