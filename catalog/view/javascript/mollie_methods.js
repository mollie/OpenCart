if (!window.mollie_methods)
{
	window.mollie_methods = function ($, method_report_url, issuer_report_url, issuer_text) {

		var self = this;

		this.methods         = [];
		this.methodReportURL = method_report_url;
		this.issuerReportURL = issuer_report_url;
		this.issuerText      = (typeof issuer_text !== "undefined" && issuer_text !== "") ? issuer_text : "Select your bank:";
		this.reloadTimeout   = null;
		this.selectedMethod  = null;

		this.addMethod = function (id, description, image, selected)
		{
			if (selected)
			{
				self.selectedMethod = id;
			}

			for (var i = 0; i < self.methods.length; i++)
			{
				if (self.methods[i].id === id)
				{
					return;
				}
			}

			var index = self.methods.length;

			self.methods.push({
				index:          index,
				id:             id,
				description:    description,
				image:          image,
				selectedIssuer: null,
				issuers:        []
			});
		};

		this.addIssuer = function (method_id, id, name, selected)
		{
			var method = self.getMethodByID(method_id);

			if (!method)
			{
				return;
			}

			if (selected)
			{
				method.selectedIssuer = id;
			}

			for (var i = 0; i < method.issuers.length; i++)
			{
				if (method.issuers[i].id === id)
				{
					return;
				}
			}

			method.issuers.push({
				id:   id,
				name: name
			});
		};

		this.getMethodByID = function (method_id)
		{
			for (var i = 0; i < self.methods.length; i++)
			{
				if (self.methods[i].id === method_id)
				{
					return self.methods[i];
				}
			}

			return null;
		};

		this.getIssuerByID = function (issuer_id)
		{
			for (var i = 0; i < self.methods.length; i++)
			{
				for (var j = 0; j < self.methods[i].issuers.length; j++)
				{
					if (self.methods[i].issuers[j].id === issuer_id)
					{
						return self.methods[i].issuers[j];
					}
				}
			}

			return null;
		};

		this.appendMethods = function ()
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
				method,
				td;

			// Quickcheckout has weird class names.
			if (row.parent().hasClass("radio-input"))
			{
				row = row.parent();
			}

			if (!mollie.length)
			{
				window.console && console.log('Error in mollie_methods_append: Cannot append non-existing method.');
				return false;
			}

			// Exit if methods already were appended.
			if (typeof mollie.data("mollie-method-id") === "string")
			{
				return false;
			}

			if (!self.methods.length)
			{
				window.console && console.log('Error in mollie_methods_append: No methods found.');
				return false;
			}

			for (var i = 0; i < self.methods.length; i++)
			{
				method = self.methods[i];

				if (!method.id || !method.description)
				{
					continue;
				}

				new_row = row.clone();
				new_row.attr("id", "");

				method_input = $('<input id="mpm_' + method.index + '" type="radio" value="mollie_ideal" name="payment_method" />');
				method_label = $('<label for="mpm_' + method.index + '"></label>');
				method_icon  = $('<img src="' + method.image + '" align="left" />');

				method_input.data("mollie-method-id", method.id);

				method_input
					.click(self.selectMethod)
					.on("remove", self.reloadMethods);

				method_icon.css({"float":"none", "height":24, "margin":"-2px 0.5em 0", "display":"inline"});

				if (method.issuers.length)
				{
					method_issuers = $('<select id="mpm_' + method.index + '_issuer"><option value="">' + self.issuerText + '</option></select>');

					method_issuers.change(self.selectIssuer);

					for (var j = 0; j < method.issuers.length; j++)
					{
						method_issuers_option = '<option value="' + method.issuers[j].id + '"';

						if (method.selectedIssuer === method.issuers[j].id)
						{
							method_issuers_option += ' selected';
						}

						method_issuers_option += '>' + method.issuers[j].name + '</option>';

						method_issuers.append(method_issuers_option);
					}

					issuers_row = row.clone();

					issuers_row
						.attr("id", "mpm_" + method.index + "_issuer_row")
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

					if (method.issuers.length)
					{
						td = issuers_row.find("td");

						td.eq(0).empty().html("&nbsp;");
						td.eq(1).empty().append(method_issuers);
					}
				}
				else
				{
					new_row.addClass("clearfix");

					method_label
						.append(method_input)
						.append(method_icon)
						.append(" &nbsp; " + method.description);

					new_row.empty().append(method_label);

					if (method.issuers.length)
					{
						issuers_row.empty().append(method_issuers);
					}
				}

				row.before(new_row);

				if (method.issuers.length)
				{
					row.before(issuers_row);
				}
			}

			row.remove();

			// Click the selected or first available payment method to trigger click events.
			if (self.selectedMethod)
			{
				method = self.getMethodByID(self.selectedMethod);

				$("#mpm_" + method.index).click();
			}
			else
			{
				$('input[name="payment_method"]:checked, input[name="payment_method"]:first').click();
			}

			return true;
		};

		// Allow AJAX modules to reload parts of the checkout process without having to load our library again.
		this.reloadMethods = function ()
		{
			clearTimeout(self.reloadTimeout);

			self.reloadTimeout = setTimeout(self.appendMethods, 100);
		};

		this.selectMethod = function ()
		{
			var method_id = $(this).data("mollie-method-id"),
				method    = self.getMethodByID(method_id);

			if (method)
			{
				self.selectedMethod = method.id;

				$.post(self.methodReportURL, {
					mollie_method_id: method.id,
					mollie_method_description: method.description
				});

				self.showIssuers(method);
			}
		};

		this.selectIssuer = function ()
		{
			var issuer_id = $(this).val(),
				method    = self.getMethodByID(self.selectedMethod),
				issuer    = self.getIssuerByID(issuer_id);

			if (issuer)
			{
				method.selectedIssuer = issuer.id;

				$.post(self.issuerReportURL, {
					mollie_issuer_id: issuer.id
				});
			}
		};

		self.showIssuers = function (method)
		{
			$(".mpm_issuer_rows").hide();

			$('#mpm_' + method.index + "_issuer_row").show();
		};
	};
}
