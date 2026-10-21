{{-- Port of frontend/src/components/auth/PasswordStrengthChecker.tsx (showDetails = true).
     Expects an Alpine scope; `$model` is the expression holding the password string. --}}
<div class="mt-2 space-y-2" x-data="passwordStrength(() => {{ $model }})">
    {{-- Strength Indicator --}}
    <div class="flex items-center space-x-2">
        <div class="flex-1 bg-gray-200 rounded-full h-2">
            <div class="h-2 rounded-full transition-all duration-300" :class="barClass"></div>
        </div>
        <span class="text-sm font-medium" :class="strength.color" x-text="strength.label"></span>
    </div>

    {{-- Requirements List --}}
    <div class="space-y-1">
        <template x-for="req in requirementList" :key="req.key">
            <div class="flex items-center space-x-2 text-sm">
                <span x-show="req.met" class="contents"><x-lucide-circle-check class="w-4 h-4 text-green-600" /></span>
                <span x-show="!req.met" class="contents"><x-lucide-circle-x class="w-4 h-4 text-red-500" /></span>
                <span :class="req.met ? 'text-green-700' : 'text-red-600'" x-text="req.text"></span>
            </div>
        </template>
    </div>

    {{-- Success Message --}}
    <template x-if="validation.isValid">
        <div class="flex items-center space-x-2 text-sm text-green-600">
            <x-lucide-circle-check class="w-4 h-4" />
            <span class="font-medium">Password meets all security requirements!</span>
        </div>
    </template>
</div>
