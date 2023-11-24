/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*******************************!*\
  !*** ./resources/js/admin.js ***!
  \*******************************/
var valueDelete = document.getElementById("delete");
var valueRow = document.getElementById("row");
valueDelete.addEventListener('click', function () {
  if (confirm("情報を消していいですか？")) valueRow.remove();
});
/******/ })()
;