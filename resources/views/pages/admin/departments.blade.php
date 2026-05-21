<x-layouts::admin :page_title="'Departments'" :title="__('Departments')">
    <div class="flex flex-1 flex-col gap-8 ">
        @livewire('admin.departments.table')
    </div>
</x-layouts::admin>
