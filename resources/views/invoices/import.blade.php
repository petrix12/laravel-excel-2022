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
