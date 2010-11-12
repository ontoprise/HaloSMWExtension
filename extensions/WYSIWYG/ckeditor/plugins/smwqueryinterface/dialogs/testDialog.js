CKEDITOR.dialog.add( 'SMWqi', function( editor ) {
	return {
		title: 'Insert Query',

		minWidth: 200,
		minHeight: 80,


		contents: [
			{
				id: 'tab1',
				label: 'Tab1',
				title: 'Tab1',
				elements : [
					{
						id: 'mytext',
						type: 'text',
						label: "Text",
						validate : function() {
							// potentielle Validierungen
							if (this.getValue() == "") {
								alert("Das Feld darf nicht leer sein!");
							}
							return this.getValue() != "";
						}
					},
					{
						id: 'mycolor',
						type: 'select',
						label: "Farbe",
						items: [
						        ['rot'],
						        ['grün'],
						        ['blau']
						       ],
						validate : function() {
							// Validierungen
							return true;
						}
					},
				 ]
			}
		 ],


		 onOk: function() {
			var color = this.getContentElement('tab1', 'mycolor').getValue();
			var numColor;
			if (color == 'rot') numColor = '#F00';
			else if (color == 'grün') numColor = '#0F0';
			else if (color == 'blau') numColor = '#00F';

			var element = CKEDITOR.dom.element.createFromHtml(
					'<span style="color:' + numColor + ';">' +
						this.getContentElement('tab1', 'mytext').getValue() +
					'</span>');
			editor.insertElement(element);
		 }

	};

} );