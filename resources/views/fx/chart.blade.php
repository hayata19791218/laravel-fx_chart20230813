<x-layout.layout :user-name="$user_name" :title="$title">
    <div class="overflow-x-auto overflow-y-hidden mt-4">
        <div class="w-[110%] h-[850px]">
            <h1 class="mb-6 sm:text-xl lg:text-[25px] text-center">ドル円のチャート表</h1>
            <chart-component 
                :chart-data='@json($chartData)'
                :is-logged-in='@json($user_name)'
            ></chart-component>
        </div>
    </div>
</x-layout.layout>