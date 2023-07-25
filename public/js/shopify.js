/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./Resources/assets/js/app.js":
/*!************************************!*\
  !*** ./Resources/assets/js/app.js ***!
  \************************************/
/***/ (() => {

var selectAllCheckbox = document.getElementById('selectAll');
var productCheckboxes = Array.from(document.getElementsByClassName('product-checkbox'));
var variantCheckboxes = Array.from(document.getElementsByClassName('variant-checkbox'));
var modal = document.getElementById('myModal');
var modalBody = document.getElementById('modalBody');
var discountButton = document.getElementById('discountButton');
var revertButton = document.getElementById('revertButton');
var closeButton = document.getElementById('closeButton');
var submitButton = document.getElementById('submitButton');
var discountTypeSelect = document.getElementById('discountType');
var discountValueInput = document.getElementById('discountValue');
var revertModal = document.getElementById('revertModal');
var revertForm = document.getElementById('revertForm');
var revertVariantIdInput = document.getElementById('revertVariantIdInput');
var revertSubmitButton = document.getElementById('revertSubmitButton');

// Xử lý sự kiện khi checkbox all thay đổi
selectAllCheckbox.addEventListener('change', function () {
  var isChecked = selectAllCheckbox.checked;
  productCheckboxes.forEach(function (checkbox) {
    checkbox.checked = isChecked;
  });
  variantCheckboxes.forEach(function (checkbox) {
    checkbox.checked = isChecked;
  });
  updateButtonStatus();
});

// Xử lý sự kiện khi checkbox product thay đổi
productCheckboxes.forEach(function (checkbox) {
  checkbox.addEventListener('change', function () {
    var isChecked = checkbox.checked;
    updateVariantCheckboxes(checkbox, isChecked);
    updateButtonStatus();
    updateSelectAllCheckbox();
  });
});

// Xử lý sự kiện khi checkbox variant thay đổi
variantCheckboxes.forEach(function (checkbox) {
  checkbox.addEventListener('change', function () {
    var productId = checkbox.dataset.productId;
    var productCheckbox = document.getElementById("productCheckbox_".concat(productId));
    updateProductCheckbox(productCheckbox);
    updateButtonStatus();
    updateSelectAllCheckbox();
  });
});

// Cập nhật trạng thái của các checkbox variant dựa trên checkbox product tương ứng
function updateVariantCheckboxes(productCheckbox, isChecked) {
  var productId = productCheckbox.dataset.productId;
  var variantCheckboxes = Array.from(document.querySelectorAll(".variant-checkbox[data-product-id=\"".concat(productId, "\"]")));
  variantCheckboxes.forEach(function (variantCheckbox) {
    variantCheckbox.checked = isChecked;
  });
}

// Cập nhật trạng thái của checkbox product dựa trên trạng thái của các checkbox variant
function updateProductCheckbox(productCheckbox) {
  var productId = productCheckbox.dataset.productId;
  var variantCheckboxes = Array.from(document.querySelectorAll(".variant-checkbox[data-product-id=\"".concat(productId, "\"]")));
  var isProductChecked = variantCheckboxes.every(function (checkbox) {
    return checkbox.checked;
  });
  productCheckbox.checked = isProductChecked;
}

// Cập nhật trạng thái của checkbox all trên th dựa trên trạng thái của các checkbox product và variant
function updateSelectAllCheckbox() {
  var isAllChecked = productCheckboxes.every(function (checkbox) {
    return checkbox.checked;
  });
  var isAnyVariantUnchecked = variantCheckboxes.some(function (checkbox) {
    return !checkbox.checked;
  });
  selectAllCheckbox.checked = isAllChecked && !isAnyVariantUnchecked;
}

// Cập nhật trạng thái của nút
function updateButtonStatus() {
  var isAnyCheckboxChecked = productCheckboxes.some(function (checkbox) {
    return checkbox.checked;
  });
  var isAnyVariantChecked = variantCheckboxes.some(function (checkbox) {
    return checkbox.checked;
  });
  if (isAnyCheckboxChecked || isAnyVariantChecked) {
    discountButton.disabled = false;
    revertButton.disabled = false;
  } else {
    discountButton.disabled = true;
    revertButton.disabled = true;
  }
}
discountButton.addEventListener('click', function () {
  var selectedVariants = getSelectedVariants();
  updateModalContent(selectedVariants);
  showModal();
});
submitButton.addEventListener('click', function () {
  var selectedVariants = getSelectedVariants();
  hideModal();
});

// Lấy danh sách các variant đã chọn
function getSelectedVariants() {
  var selectedVariants = [];
  variantCheckboxes.forEach(function (checkbox) {
    if (checkbox.checked) {
      selectedVariants.push(checkbox.value);
    }
  });
  return selectedVariants;
}
function updateModalContent(selectedVariants) {
  var variantIdInput = document.getElementById('variantIdInput');
  variantIdInput.value = selectedVariants.join(',');
}

// Hiển thị modal
function showModal() {
  var myModal = new bootstrap.Modal(document.getElementById('myModal'));
  myModal.show();
}

// Ẩn modal
closeButton.addEventListener('click', function () {
  hideModal();
});

// Ẩn modal
function hideModal() {
  var myModal = document.getElementById('myModal');
  var bootstrapModal = bootstrap.Modal.getInstance(myModal);
  bootstrapModal.hide();
}
discountTypeSelect.addEventListener('change', function () {
  var selectedOption = discountTypeSelect.value;
  if (selectedOption === 'percent') {
    discountValueInput.min = '';
    discountValueInput.max = 99;
    discountValueInput.step = '0.01';
  } else {
    discountValueInput.min = 1;
    discountValueInput.max = '';
    discountValueInput.step = '0.01';
  }
});
revertButton.addEventListener('click', function () {
  var selectedVariants = getSelectedVariants();
  updateRevertModalContent(selectedVariants);
  showRevertModal();
});
revertSubmitButton.addEventListener('click', function () {
  var selectedVariants = getSelectedVariants();
  updateModalContent(selectedVariants);
  hideRevertModal();
});

// Cập nhật nội dung modal "Revert"
function updateRevertModalContent(selectedVariants) {
  revertVariantIdInput.value = selectedVariants.join(',');
}

// Hiển thị modal "Revert"
function showRevertModal() {
  var modal = new bootstrap.Modal(revertModal);
  modal.show();
}

// Ẩn modal "Revert"
function hideRevertModal() {
  var modal = bootstrap.Modal.getInstance(revertModal);
  modal.hide();
}
var disableCheckbox = document.getElementById('collectionCheckbox');
var elementsToDisable = document.querySelectorAll('#normalSearch input');
var selectToEnable = document.getElementById('selectCollection');
disableCheckbox.addEventListener('change', function () {
  if (this.checked) {
    elementsToDisable.forEach(function (element) {
      element.disabled = true;
      element.value = '';
    });
    selectToEnable.disabled = false;
  } else {
    elementsToDisable.forEach(function (element) {
      element.disabled = false;
    });
    selectToEnable.disabled = true;
  }
});

/***/ }),

/***/ "./Resources/assets/sass/app.scss":
/*!****************************************!*\
  !*** ./Resources/assets/sass/app.scss ***!
  \****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	(() => {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var [chunkIds, fn, priority] = deferred[i];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"/js/shopify": 0,
/******/ 			"css/shopify": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			var [chunkIds, moreModules, runtime] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = self["webpackChunk"] = self["webpackChunk"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	__webpack_require__.O(undefined, ["css/shopify"], () => (__webpack_require__("./Resources/assets/js/app.js")))
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["css/shopify"], () => (__webpack_require__("./Resources/assets/sass/app.scss")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;