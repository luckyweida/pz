$(function() {
	$('.i-checks').iCheck({
		checkboxClass: 'icheckbox_square-green',
		radioClass: 'iradio_square-green',
	});

	Handlebars.registerHelper('compare', function(lvalue, rvalue, options) {
		if (arguments.length < 3)
			throw new Error("Handlerbars Helper 'compare' needs 2 parameters");

		var operator = options.hash.operator || "==";
		var operators = {
			'==':       function(l,r) { return l == r; },
			'===':      function(l,r) { return l === r; },
			'!=':       function(l,r) { return l != r; },
			'<':        function(l,r) { return l < r; },
			'>':        function(l,r) { return l > r; },
			'<=':       function(l,r) { return l <= r; },
			'>=':       function(l,r) { return l >= r; },
			'typeof':   function(l,r) { return typeof l == r; }
		}

		if (!operators[operator])
			throw new Error("Handlerbars Helper 'compare' doesn't know the operator "+operator);

		var result = operators[operator](lvalue,rvalue);
		if( result ) {
			return options.fn(this);
		} else {
			return options.inverse(this);
		}
	});

	Handlebars.registerHelper('ifNotEmpty', function(array, options) {
        array = array && typeof array == 'object' ? array : [];
        return array.length > 0 ? options.fn(this) : options.inverse(this);
	});

	Handlebars.registerHelper('ifMoreThan1', function(array, options) {
        array = array && typeof array == 'object' ? array : [];
        return array.length > 1 ? options.fn(this) : options.inverse(this);
	});

    Handlebars.registerHelper('ifHasIt', function(array, item, options) {
        array = array && typeof array == 'object' ? array : [];
        return array.indexOf(item) !== -1 ? options.fn(this) : options.inverse(this);
    });

    Handlebars.registerHelper('ifGetByKey', function(array, key, options) {
        array = array && typeof array == 'object' ? array : [];
        return !array[key] ? options.fn(this) : options.inverse(this);
    });

	Handlebars.registerHelper('length', function(array) {
        array = array && typeof array == 'object' ? array : [];
        return array.length;
	});

	Handlebars.registerHelper('getByKey', function(array, key) {
        array = array && typeof array == 'object' ? array : [];
        return array[key];
	});

    Handlebars.registerHelper('getByKey0', function(array, key) {
        array = array && typeof array == 'object' ? array : [];
        return array[key] ? array[key] : 0;
    });

	Handlebars.registerHelper('getByKeyAndCompare', function(array, key, value, options) {
        array = array && typeof array == 'object' ? array : [];
        if (typeof array[key] == 'object') {
			return array[key] && array[key].indexOf(value) != -1 ? options.fn(this) : options.inverse(this);
		} else {
			return array[key] && array[key] == value ? options.fn(this) : options.inverse(this);
		}
	});
});
