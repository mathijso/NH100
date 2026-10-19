import Alpine from 'alpinejs';
import { planner } from './nh100';

window.Alpine = Alpine;

Alpine.data('planner', planner);

Alpine.start();
