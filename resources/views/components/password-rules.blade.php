<div class="bg-blue-100 border border-blue-400 text-blue-800 px-4 py-3 rounded-md mb-6">
    <div class="flex justify-between items-center">
        <div class="flex items-center">
            <x-icon name="information-circle" class="w-7 h-7 text-blue-500 mr-4" />
            <p class="font-bold">Regras para a senha</p>
        </div>
        <button type="button" @click="showRules = !showRules"
            class="text-sm text-blue-600 hover:underline cursor-pointer flex items-center transition-all">
            <span x-show="!showRules" class="flex items-center">
                Mostrar
                <x-icon name="chevron-down-solid" class="w-4 h-4 ml-1" />
            </span>
            <span x-show="showRules" class="flex items-center">
                Ocultar
                <x-icon name="chevron-up-solid" class="w-4 h-4 ml-1" />
            </span>
        </button>
    </div>
    <ul class="list-disc list-inside text-sm overflow-hidden transition-[max-height] duration-300 ease-in-out"
        :class="{ 'mt-3': showRules }" x-bind:style="showRules ? 'max-height: 1000px' : 'max-height: 0px'" x-cloak>
        <li>Mínimo de 8 caracteres e máximo de 64 caracteres</li>
        <li>Deve conter pelo menos uma letra maiúscula e uma minúscula</li>
        <li>Deve conter pelo menos um número</li>
        <li>Deve conter pelo menos um símbolo</li>
        <li>Não deve ser uma senha comprometida</li>
    </ul>
</div>
