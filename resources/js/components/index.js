import { landingPage, walkthroughModal } from './walkthrough';
import { passwordStrength } from '../password-validation';

export function registerComponents(Alpine) {
    Alpine.data('landingPage', landingPage);
    Alpine.data('walkthroughModal', walkthroughModal);
    Alpine.data('passwordStrength', passwordStrength);
}
