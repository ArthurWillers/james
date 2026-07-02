import Alpine from 'alpinejs';
import EasyMDE from 'easymde';
import 'easymde/dist/easymde.min.css';
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';

window.EasyMDE = EasyMDE;
window.Cropper = Cropper;

import * as echarts from 'echarts';
window.echarts = echarts;

window.Alpine = Alpine;

Alpine.start();