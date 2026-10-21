/** Port of frontend/src/utils/passwordValidation.ts */
export function validatePassword(password) {
    const requirements = {
        minLength: password.length >= 8,
        hasUppercase: /[A-Z]/.test(password),
        hasLowercase: /[a-z]/.test(password),
        hasNumber: /\d/.test(password),
    };

    const feedback = [];
    if (!requirements.minLength) feedback.push('Password must be at least 8 characters long');
    if (!requirements.hasUppercase) feedback.push('Password must contain at least one uppercase letter');
    if (!requirements.hasLowercase) feedback.push('Password must contain at least one lowercase letter');
    if (!requirements.hasNumber) feedback.push('Password must contain at least one number');

    const requirementsMet = Object.values(requirements).filter(Boolean).length;
    let score = requirementsMet;
    if (password.length >= 12) score += 0.5;
    if (password.length >= 16) score += 0.5;
    score = Math.min(4, Math.floor(score));

    const isValid = Object.values(requirements).every(Boolean);
    return { isValid, score, requirements, feedback };
}

export function getPasswordStrength(score) {
    switch (score) {
        case 0:
        case 1:
            return { label: 'Very Weak', color: 'text-red-600', bgColor: 'bg-red-100' };
        case 2:
            return { label: 'Weak', color: 'text-red-500', bgColor: 'bg-red-50' };
        case 3:
            return { label: 'Good', color: 'text-yellow-600', bgColor: 'bg-yellow-100' };
        case 4:
            return { label: 'Strong', color: 'text-green-600', bgColor: 'bg-green-100' };
        default:
            return { label: 'Very Weak', color: 'text-red-600', bgColor: 'bg-red-100' };
    }
}

const REQUIREMENT_TEXT = {
    minLength: '8 characters',
    hasUppercase: '1 uppercase letter',
    hasLowercase: '1 lowercase letter',
    hasNumber: '1 number',
};

/** Alpine component backing partials/password-strength.blade.php (PasswordStrengthChecker.tsx). */
export function passwordStrength(getPassword) {
    return {
        get password() {
            return getPassword() || '';
        },
        get validation() {
            return validatePassword(this.password);
        },
        get strength() {
            return getPasswordStrength(this.validation.score);
        },
        get barClass() {
            const s = this.validation.score;
            return s === 0 ? 'bg-red-500 w-1/4' : s === 1 ? 'bg-red-400 w-2/4' : s === 2 ? 'bg-yellow-500 w-3/4' : s === 3 ? 'bg-yellow-400 w-4/4' : 'bg-green-500 w-full';
        },
        get requirementList() {
            return Object.entries(this.validation.requirements).map(([key, met]) => ({ key, met, text: REQUIREMENT_TEXT[key] || '' }));
        },
    };
}
