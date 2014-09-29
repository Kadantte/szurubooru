var App = App || {};

App.Keyboard = function(mousetrap) {

	function keyup(key, callback) {
		mousetrap.bind(key, simpleKeyPressed(callback), 'keyup');
	}

	function keydown(key, callback) {
		mousetrap.bind(key, simpleKeyPressed(callback));
	}

	function simpleKeyPressed(callback) {
		return function(e) {
			if (!e.altKey && !e.ctrlKey) {
				callback();
			}
		};
	}

	function reset() {
		mousetrap.reset();
	}

	return {
		keydown: keydown,
		keyup: keyup,
		reset: reset,
	};
};

App.DI.register('keyboard', ['mousetrap'], App.Keyboard);
