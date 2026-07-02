const fs = require('fs');
const path = require('path');

const assets = [
	'flatpickr/dist/l10n',
	'select2/dist/js/i18n',
	'intl-tel-input/build/js/i18n',
	'intl-tel-input/build/img',
	'jquery-ui-touch-punch/jquery.ui.touch-punch.min.js',
	'blueimp-file-upload/js/jquery.fileupload.js',
	'@fancyapps/fancybox/dist/jquery.fancybox.min.js',
	'slick-carousel/slick/slick.min.js',
	'sticky-sidebar/dist/jquery.sticky-sidebar.min.js',
	'php-date-formatter/js/php-date-formatter.min.js',
	'flatpickr/dist/flatpickr.min.js',
	'select2/dist/js/select2.full.min.js',
	'intl-tel-input/build/js/intlTelInput.min.js',
	'intl-tel-input/build/js/utils.js',
	'chart.js/dist/chart.min.js',
	'chartjs-adapter-moment/dist/chartjs-adapter-moment.min.js',
	'@fancyapps/fancybox/dist/jquery.fancybox.min.css',
	'slick-carousel/slick/slick.css',
	'flatpickr/dist/flatpickr.min.css',
	'select2/dist/css/select2.min.css',
	'intl-tel-input/build/css/intlTelInput.min.css',
];

assets.forEach((asset) => {
	const src = path.join('node_modules', asset);
	const dest = path.join('assets/vendor', asset);
	const isDir = fs.statSync(src).isDirectory();
	const destDir = isDir ? dest : path.dirname(dest);

	if (!fs.existsSync(destDir)) {
		fs.mkdirSync(destDir, { recursive: true });
	}

	if (isDir) {
		fs.cpSync(src, dest, { recursive: true });
	} else {
		fs.copyFileSync(src, dest);
	}
});
