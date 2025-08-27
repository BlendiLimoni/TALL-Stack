<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="p-4 rounded-lg bg-white dark:bg-gray-800 shadow">
                            <div class="text-sm text-gray-500">Welcome</div>
                            <div class="mt-1 text-lg">{{ Auth::user()->name }}</div>
                        </div>
                        <div class="p-4 rounded-lg bg-white dark:bg-gray-800 shadow">
                            <div class="text-sm text-gray-500">Current Team</div>
                            <div class="mt-1 text-lg">{{ Auth::user()->currentTeam->name ?? 'N/A' }}</div>
                        </div>
                        <a href="{{ route('projects.index') }}" class="p-4 rounded-lg bg-indigo-600 text-white shadow hover:bg-indigo-700">
                            <div class="text-sm opacity-90">Manage</div>
                            <div class="mt-1 text-lg font-semibold">Projects →</div>
                        </a>
                    </div>
                    <div class="p-6 rounded-lg bg-white dark:bg-gray-800 shadow">
                        <div class="text-gray-600 dark:text-gray-300">This is your Smart Workspace Manager. Use Teams to invite members and manage projects with a Kanban board.</div>
                    </div>
            </div>
        </div>
    </div>
</x-app-layout>
