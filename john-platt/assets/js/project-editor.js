(() => {
	'use strict';
	const setup = () => {
		const editor = window.wp?.data?.dispatch('core/edit-post');
		if (editor?.removeEditorPanel) editor.removeEditorPanel('taxonomy-panel-project_category');
		document.querySelectorAll('.jp-project-category-controls').forEach(group => {
			if (group.dataset.prepared) return;
			group.dataset.prepared = 'true';
			const button = group.querySelector('.jp-add-project-category');
			const list = group.querySelector('.jp-project-category-selects');
			if (button && list) {
				button.hidden = false;
				button.addEventListener('click', () => {
					const row = list.querySelector('p').cloneNode(true);
					row.querySelector('select').value = '0';
					list.append(row);
					row.querySelector('select').focus();
				});
			}
			const flag = group.closest('#john-platt-writing-project')?.querySelector('[name="john_platt_show_project"]');
			if (flag) {
				const update = () => { group.hidden = !flag.checked; };
				flag.addEventListener('change', update);
				update();
			}
		});
	};
	if (window.wp?.domReady) wp.domReady(setup);
	else if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', setup, { once: true });
	else setup();
})();