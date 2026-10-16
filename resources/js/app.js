import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';

// Components are registered on `alpine:init` (see itsn.js, ported from the Phase 1 prototype).
window.Alpine = Alpine;
import './itsn.js';

Alpine.plugin(focus);
Alpine.start();
