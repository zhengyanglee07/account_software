// function b64e(str) {
// 	return btoa(encodeURIComponent(str).replace(/%([0-9A-F]{2})/g, (match, p1) => {
// 		return String.fromCharCode('0x' + p1);
// 	}));
// }

// function b64d(str) {
// 	return decodeURIComponent(Array.prototype.map.call(atob(str), function (c) {
// 		return '%' + c.charCodeAt(0).toString(16);
// 	}).join(''));
// }

// export function setLocalStorage(key, value) {
// 	const newValue = {};
// 	Object.keys(value).forEach((val) => {
// 		newValue[val] = b64e(value[val]);
// 	});
// 	this.setItem(key, b64e(JSON.stringify(newValue)));
// }
// export function getLocalStorage(key) {
// 	const newValue = localStorage.getItem(key);
// 	return b64d(newValue) && JSON.parse(b64d(newValue), (k) => {
// 		return (key) ? b64d(this[key]) : this[key];
// 	});
// }




// Storage.prototype.setObjectHash = function (key, myObject) {
// 	var newObject = {};
// 	Object.keys(myObject).map(function (value, index) {
// 		newObject[value] = b64e(myObject[value]);
// 	});
// 	this.setItem(key, b64e(JSON.stringify(newObject)));
// }

// Storage.prototype.getObjectHash = function (key) {
// 	var myObject = this.getItem(key);
// 	return b64d(myObject) && JSON.parse(b64d(myObject), function (key, value) {
// 		return (key) ? b64d(this[key]) : this[key];
// 	});
// }

// // Usage
// const userObject = { userId: 24, name: 'Jac|`` /\Baèéàòùu"aer' };
// document.body.appendChild(document.createTextNode('user: ' + JSON.stringify(userObject)));
// document.body.appendChild(document.createElement('br'));
// localStorage.setObjectHash('user', userObject);
// document.body.appendChild(document.createTextNode('localStorage.getItem(\'user\'): ' + localStorage.getItem('user')));
// document.body.appendChild(document.createElement('br'));
// document.body.appendChild(document.createTextNode('localStorage.getObject(\'user\').name: ' + localStorage.getObjectHash('user').name));