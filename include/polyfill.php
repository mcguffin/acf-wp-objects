<?php

if ( ! function_exists('array_any') ) {
	function array_any(array $array, callable $callback): bool {
		foreach ($array as $key => $value) {
			if ($callback($value, $key)) {
				return true;
			}
		}

		return false;
	}
}

if ( ! function_exists('array_all') ) {
	function array_all(array $array, callable $callback): bool {
		foreach ($array as $key => $value) {
			if (!$callback($value, $key)) {
				return false;
			}
		}

		return true;
	}
}

if ( ! function_exists('array_find') ) {
	if (\PHP_VERSION_ID >= 80000) {
		function array_find(array $array, callable $callback): mixed {
			foreach ($array as $key => $value) {
				if ($callback($value, $key)) {
					return $value;
				}
			}

			return null;
		}
	} else {
		function array_find(array $array, callable $callback) {
			foreach ($array as $key => $value) {
				if ($callback($value, $key)) {
					return $value;
				}
			}

			return null;
		}

	}
}

if ( ! function_exists('array_find_key') ) {
	if (\PHP_VERSION_ID >= 80000) {
		function array_find_key(array $array, callable $callback): mixed {
			foreach ($array as $key => $value) {
				if ($callback($value, $key)) {
					return $key;
				}
			}

			return null;
		}
	} else {
		function array_find_key(array $array, callable $callback) {
			foreach ($array as $key => $value) {
				if ($callback($value, $key)) {
					return $key;
				}
			}

			return null;
		}
	}
}
