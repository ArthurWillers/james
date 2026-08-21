<div class="bg-blue-100 border border-blue-400 text-blue-800 px-4 py-3 rounded-md mb-6" x-data="{ showRules: false }">
    <div class="flex justify-between items-center">
        <div class="flex items-center">
            <x-heroicon-o-information-circle class="w-7 h-7 text-blue-500 mr-4" />
            <p class="font-bold">Regras para a senha</p>
        </div>
        <button type="button" @click="showRules = !showRules"
            class="text-sm text-blue-600 hover:underline cursor-pointer flex items-center transition-all">
            <span class="flex items-center" x-show="!showRules">
                Mostrar
                <x-heroicon-m-chevron-down class="w-4 h-4 ml-1" />
            </span>
            <span class="flex items-center" x-show="showRules" x-cloak>
                Ocultar
                <x-heroicon-m-chevron-up class="w-4 h-4 ml-1" />
            </span>
        </button>
    </div>
    
    <div class="grid transition-all motion-duration-fast motion-ease-smooth-out" :class="showRules ? 'grid-rows-[1fr] opacity-100 mt-3' : 'grid-rows-[0fr] opacity-0'">
        <div class="overflow-hidden">
            <ul class="list-disc list-inside text-sm">
                <li>Mínimo de 8 caracteres e máximo de 64 caracteres</li>
                <li>Deve conter pelo menos uma letra maiúscula e uma minúscula</li>
                <li>Deve conter pelo menos um número</li>
                <li>Deve conter pelo menos um símbolo</li>
                <li>Não deve ser uma senha comprometida</li>
            </ul>
        </div>
    </div>
</div>
