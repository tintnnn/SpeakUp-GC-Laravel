import { landingPage, walkthroughModal } from './walkthrough';
import { passwordStrength } from '../password-validation';
import { profileSetup } from './profile-setup';
import { pushSettings } from './push-settings';

export function registerComponents(Alpine) {
    Alpine.data('landingPage', landingPage);
    Alpine.data('walkthroughModal', walkthroughModal);
    Alpine.data('passwordStrength', passwordStrength);
    Alpine.data('profileSetup', profileSetup);
    Alpine.data('pushSettings', pushSettings);
}
