const BranchSwitcher = function() {
	let form;
	let branchForm;
	let config = {
		'endpoint': '',
		'branches': [],
		'activeBranch': '',
		'commands': {}
	};

	function mergeConfig(data) {
		config = {...config, ...data};
	}

	function init() {
		createElements()
			.then(function() {
				BranchForm(form, config).run();
			});
	}

	async function createElements() {
		let container = createElement('div', { id: 'branch-switcher', style: 'display: none;' });

		let form_attributes = {
			method: 'post',
			class: 'js-branch-switcher'
		}
		if (config.error) {
			form_attributes['data-error'] = config.error;
		}
		form = createElement('form', form_attributes);

		// Return the form if there's an error
		if (config.error) {
			// Append the form to the container
			container.appendChild(form);

			// Append the container to the document
			return document.body.appendChild(container);
		}

		let branches_select = createElement('select', { name: 'branch' });
		let commands_container = createElement('details');
		let commands_summary = createElement('summary');
		let commands_ul = createElement('ul');
		let submit = createElement('input', { type: 'submit', value: 'Switch' });
		let notice = createElement('div', { class: 'notice' });

		commands_summary.append(document.createTextNode('opties'));

		// Populate the select with all the branches
		config.branches.forEach(option => {
			let child = createElement('option', { value: option });
				child.textContent = option;

			if (option == config.activeBranch) {
				child.setAttribute('selected', true);
			}

			branches_select.append(child);
		});

		// Create checkboxes for each command
		for (let command in config.commands) {
			const checkbox_id = command.replaceAll(' ', '-');
			const options = config.commands[command];

			// Create checkbox
			let checkbox = createElement('input', { type: 'checkbox', value: command, name: 'commands[]', id: checkbox_id });
			if (options.default == 1) {
				checkbox.setAttribute('checked', true);
			}

			// Create label
			let label = createElement('label', { for: checkbox_id });
				label.append(document.createTextNode(command));

			// Create LI and append the checkbox + label
			let li = createElement('li');
				li
					.append(checkbox)
					.append(label);

			commands_ul.append(li);
		}

		// Create possibility to add own command
		let textfield_li = createElement('li');
		let textfield = createElement('input', { type: 'text', name: 'own_command', placeholder: 'run custom command..' });
		textfield_li.append(textfield);
		commands_ul.append(textfield_li);

		// Group all command elements in a container
		commands_container
			.append(commands_summary)
			.append(commands_ul);

		// Append all elements to the form
		form
			.append(branches_select)
			.append(commands_container)
			.append(notice)
			.append(submit);

		// Append the form to the container
		container.appendChild(form);

		// Append the container to the document
		return document.body.appendChild(container);
	}

	// Helper function for creating new elements
	function createElement(element, attributes = {}) {
		let el = document.createElement(element);

		for (key in attributes) {
			el.setAttribute(key, attributes[key]);
		}

		el.append = child => {
			el.appendChild(child);

			return el;
		};

		return el;
	}

	return {
		run: init,
		setData: mergeConfig
	}
}();

const BranchForm = function(form, config) {
	const container = form.parentNode;
	let is_visible = container.style.display != 'none';

	function run() {
		setEventListeners();
	}

	function setEventListeners() {
		form.addEventListener('submit', submitForm);
		container.addEventListener('click', clickListener);
		document.addEventListener('keydown', keyListener);
	}

	function keyListener(event) {
		if (event.ctrlKey && event.keyCode == 66) {
			// ctrl + b
			toggle();
		}
		else if (event.keyCode == 27 && is_visible) {
			// esc
			toggle();
		}
	}

	function clickListener(event) {
		if (! form.contains(event.target)) {
			toggle();
		}
	}

	function toggle() {
		container.style.display = is_visible ? 'none' : 'block';
		is_visible = ! is_visible;
	}

	function submitForm(event) {
		event.preventDefault();

		let branch = form.querySelector('option:checked').value;
		let submit = form.querySelector('input[type="submit"]');
		let notice = form.querySelector('.notice');
			notice.innerHTML = getNoticeHtml();
			notice.style.display = 'block';

		submit.setAttribute('disabled', true);

		fetch(config.endpoint, {
			method: 'POST',
			body: new FormData(form)
		})
			.then(resp => resp.json())
			.then(resp => {
				if (resp.output) {
					console.clear();
					for (key in resp.output) {
						console.log(`%c ${key} ▼`, 'color: #0b9bee; font-weight: bold; font-size: 14px;');
						console.dir(resp.output[key]);
					}
				}

				if (! resp.switched) {
					submit.removeAttribute('disabled');
				}

				notice.innerHTML = getNoticeHtml(resp);
			});
	}

	function getNoticeHtml(response = {}) {
		const running = Object.keys(response).length == 0;
		let html = `<ul>`;
			html += `<li><span>status: </span>${running ? 'running..' : 'finished'}</li>`;
			html += `<li><span>error: </span>${response.error ? 'yes' : 'no'}</li>`;
			html += `<li><span>switched: </span>${response.switched ? 'yes' : 'no'}</li>`;
			html += `<li><span>message: </span>${response.message ? response.message + '<br>Check console for additional output' : '-'}</li>`;
			html += `</ul>`;

		return html;
	}

	return {
		run,
	};
};
