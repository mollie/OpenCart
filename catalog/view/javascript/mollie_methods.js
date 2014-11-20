if (!window.mollie_method_add)
{
	(function ($)
	{
		window.mollie_method_add = function (id, description, image)
		{
			if (!window.mollie_methods)
			{
				window.mollie_methods = [];
			}

			for (var i = 0; i < window.mollie_methods.length; i++)
			{
				if (window.mollie_methods[i].id == id)
				{
					return window.mollie_methods;
				}
			}

			window.mollie_methods.push({ id: id, description: description, image: image });

			return window.mollie_methods;
		};

		window.mollie_issuer_add = function (method_id, id, name, selected)
		{
			if (!window.mollie_issuers)
			{
				window.mollie_issuers = [];
			}

			for (var i = 0; i < window.mollie_methods.length; i++)
			{
				if (window.mollie_methods[i].id == method_id)
				{
					if (!window.mollie_issuers[i])
					{
						window.mollie_issuers[i] = [];
					}

					for (var j = 0; j < window.mollie_issuers[i].length; j++)
					{
						if (window.mollie_issuers[i][j].id == id)
						{
							window.mollie_issuers[i][j].selected = selected;

							return window.mollie_issuers[i];
						}
					}

					window.mollie_issuers[i].push({ id: id, name: name, selected: selected });

					return window.mollie_issuers[i];
				}
			}

			return [];
		};

		window.mollie_get_issuers = function (method_id)
		{
			if (typeof window.mollie_issuers[method_id] !== "object")
			{
				return [];
			}

			return window.mollie_issuers[method_id];
		};

		window.mollie_methods_append = function (method_report_url, issuer_report_url, issuer_text, methods)
		{
			var mollie    = $('input[name="payment_method"][value="mollie_ideal"]'),
				row       = mollie.closest(".radio, tr"),
				use_table = (row.is("tr")),
				new_row,
				issuers_row,
				method_input,
				method_label,
				method_icon,
				method_issuers,
				method_issuers_option,
				issuers,
				method,
				td,
				m,
				i;

			if (!mollie.length)
			{
				window.console && console.log('Error in mollie_methods_append: Cannot append non-existing method.');
				return false;
			}

			if (typeof methods === "undefined" || !methods.length)
			{
				methods = window.mollie_methods;
			}

			if (!methods.length)
			{
				window.console && console.log('Error in mollie_methods_append: No methods found.');
				return false;
			}

			if (typeof issuer_text === "undefined" || issuer_text == '')
			{
				issuer_text = 'Select your bank:';
			}

			for (m = 0; m < methods.length; m++)
			{
				method = methods[m];

				if (!method.id || !method.description)
				{
					continue;
				}

				issuers = mollie_get_issuers(m);

				new_row = row.clone();
				new_row.attr("id", "");

				method_input = $('<input id="mpm_' + m + '" type="radio" value="mollie_ideal" name="payment_method" onclick="window.mollie_method_select(\'' + method_report_url + '\', \'' + method.id + '\', \'' + method.description + '\', \'mpm_' + m + '_issuer_row\');" />');
				method_label = $('<label for="mpm_' + m + '"></label>');
				method_icon  = $('<img src="' + method.image + '" height="24" align="left" />');

				if (issuers.length)
				{
					method_issuers = $('<select id="mpm_' + m + '_issuer" onchange="mollie_issuer_select(\'' + issuer_report_url + '\', (window.jQuery || window.$)(this).val())"><option value="">' + issuer_text + '</option></select>');

					for (i = 0; i < issuers.length; i++)
					{
						method_issuers_option = '<option value="' + issuers[i].id + '"';

						if (issuers[i].selected)
						{
							method_issuers_option += ' selected';
						}

						method_issuers_option += '>' + issuers[i].name + '</option>';

						method_issuers.append(method_issuers_option);
					}

					issuers_row = row.clone();

					issuers_row
						.attr("id", "mpm_" + m + "_issuer_row")
						.addClass("mpm_issuer_rows");
				}

				if (use_table)
				{
					td = new_row.find("td");

					td.eq(0).empty().append(method_input);

					method_icon.css({"marginTop":-5});

					method_label
						.append(method_icon)
						.append(" &nbsp; " + method.description);

					td.eq(1).empty().append(method_label);

					if (issuers.length)
					{
						td = issuers_row.find("td");

						td.eq(0).empty().html("&nbsp;");
						td.eq(1).empty().append(method_issuers);
					}
				}
				else
				{
					new_row.addClass("clearfix");

					method_icon.css({"marginTop":-2});

					method_label
						.append(method_input)
						.append(method_icon)
						.append(" &nbsp; " + method.description);

					new_row.empty().append(method_label);

					if (issuers.length)
					{
						issuers_row.empty().append(method_issuers);
					}
				}

				row.before(new_row);

				if (issuers.length)
				{
					row.before(issuers_row);
				}
			}

			row.remove();

			return true;
		};

		window.mollie_method_select = function (report_url, method_id, method_description, issuers_row)
		{
			$.post(report_url, {
				mollie_method_id:          method_id,
				mollie_method_description: method_description
			});

			mollie_display_issuers(issuers_row);
		};

		window.mollie_issuer_select = function (report_url, issuer_id)
		{
			$.post(report_url, {
				mollie_issuer_id: issuer_id
			});
		};

		window.mollie_display_issuers = function (active_issuers_row)
		{
			$('.mpm_issuer_rows').hide();

			if (typeof active_issuers_row !== "undefined" && active_issuers_row !== '')
			{
				$('#' + active_issuers_row).show();
			}
		};

	}) (window.jQuery || window.$);
}
