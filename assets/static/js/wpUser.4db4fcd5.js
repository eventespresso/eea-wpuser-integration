/*! For license information please see wpUser.4db4fcd5.js.LICENSE.txt */
this.eventespresso=this.eventespresso||{},this.eventespresso.wpUser=function(e){var t={};function r(n){if(t[n])return t[n].exports;var o=t[n]={i:n,l:!1,exports:{}};return e[n].call(o.exports,o,o.exports,r),o.l=!0,o.exports}return r.m=e,r.c=t,r.d=function(e,t,n){r.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:n})},r.r=function(e){"undefined"!==typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},r.t=function(e,t){if(1&t&&(e=r(e)),8&t)return e;if(4&t&&"object"===typeof e&&e&&e.__esModule)return e;var n=Object.create(null);if(r.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var o in e)r.d(n,o,function(t){return e[t]}.bind(null,o));return n},r.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return r.d(t,"a",t),t},r.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},r.p="/",r(r.s=16)}([function(e,t){e.exports=window.React},function(e,t){e.exports=window.eventespresso.edtrServices},function(e,t){e.exports=window.eventespresso.i18n},function(e,t,r){var n=r(17),o=r(18),i=r(9),c=r(19);e.exports=function(e,t){return n(e)||o(e,t)||i(e,t)||c()}},function(e,t){function r(){return e.exports=r=Object.assign||function(e){for(var t=1;t<arguments.length;t++){var r=arguments[t];for(var n in r)Object.prototype.hasOwnProperty.call(r,n)&&(e[n]=r[n])}return e},r.apply(this,arguments)}e.exports=r},function(e,t){e.exports=function(e,t,r){return t in e?Object.defineProperty(e,t,{value:r,enumerable:!0,configurable:!0,writable:!0}):e[t]=r,e}},function(e,t,r){var n=r(20),o=r(21),i=r(9),c=r(22);e.exports=function(e){return n(e)||o(e)||i(e)||c()}},function(e,t,r){var n=r(23);e.exports=function(e,t){if(null==e)return{};var r,o,i=n(e,t);if(Object.getOwnPropertySymbols){var c=Object.getOwnPropertySymbols(e);for(o=0;o<c.length;o++)r=c[o],t.indexOf(r)>=0||Object.prototype.propertyIsEnumerable.call(e,r)&&(i[r]=e[r])}return i}},function(e,t){e.exports=window.eventespresso.data},function(e,t,r){var n=r(10);e.exports=function(e,t){if(e){if("string"===typeof e)return n(e,t);var r=Object.prototype.toString.call(e).slice(8,-1);return"Object"===r&&e.constructor&&(r=e.constructor.name),"Map"===r||"Set"===r?Array.from(e):"Arguments"===r||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r)?n(e,t):void 0}}},function(e,t){e.exports=function(e,t){(null==t||t>e.length)&&(t=e.length);for(var r=0,n=new Array(t);r<t;r++)n[r]=e[r];return n}},function(e,t){e.exports=window.eventespresso.registry},function(e,t){e.exports=window.eventespresso.utils},function(e,t){e.exports=window.eventespresso.hooks},function(e,t,r){var n;!function(){"use strict";var r={}.hasOwnProperty;function o(){for(var e=[],t=0;t<arguments.length;t++){var n=arguments[t];if(n){var i=typeof n;if("string"===i||"number"===i)e.push(n);else if(Array.isArray(n)&&n.length){var c=o.apply(null,n);c&&e.push(c)}else if("object"===i)for(var u in n)r.call(n,u)&&n[u]&&e.push(u)}}return e.join(" ")}e.exports?(o.default=o,e.exports=o):void 0===(n=function(){return o}.apply(t,[]))||(e.exports=n)}()},function(e,t){e.exports=window.eventespresso.predicates},function(e,t,r){e.exports=r(24)},function(e,t){e.exports=function(e){if(Array.isArray(e))return e}},function(e,t){e.exports=function(e,t){if("undefined"!==typeof Symbol&&Symbol.iterator in Object(e)){var r=[],n=!0,o=!1,i=void 0;try{for(var c,u=e[Symbol.iterator]();!(n=(c=u.next()).done)&&(r.push(c.value),!t||r.length!==t);n=!0);}catch(a){o=!0,i=a}finally{try{n||null==u.return||u.return()}finally{if(o)throw i}}return r}}},function(e,t){e.exports=function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}},function(e,t,r){var n=r(10);e.exports=function(e){if(Array.isArray(e))return n(e)}},function(e,t){e.exports=function(e){if("undefined"!==typeof Symbol&&Symbol.iterator in Object(e))return Array.from(e)}},function(e,t){e.exports=function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}},function(e,t){e.exports=function(e,t){if(null==e)return{};var r,n,o={},i=Object.keys(e);for(n=0;n<i.length;n++)r=i[n],t.indexOf(r)>=0||(o[r]=e[r]);return o}},function(e,t,r){"use strict";r.r(t);var n=r(11),o=r(1),i=r(3),c=r.n(i),u=r(0),a=r.n(u),s=function(){return Object(u.useMemo)((function(){var e,t;return(null===(e=window)||void 0===e||null===(t=e.eventEspressoData)||void 0===t?void 0:t.wpUserData)||{}}),[])},f=function(){var e=Object(o.useIsRehydrated)(),t=c()(e,1)[0],r=s().ticketsMeta,n=Object(o.useTicketsMeta)().mergeMetaMap,i=Object(u.useRef)(!1);return Object(u.useEffect)((function(){!i.current&&t&&(n(r),i.current=!0)}),[t,n,r]),i.current},l=r(5),p=r.n(l),b=r(12),d=r(13),y="wpUser",v=r(6),m=r.n(v),O=r(2),j=function(){return Object(u.useMemo)((function(){var e,t,r=null===(e=window.eventEspressoData)||void 0===e||null===(t=e.wpUserData)||void 0===t?void 0:t.capabilityOptions,n=Object.entries(r).map((function(e){var t=c()(e,2),r=t[0],n=t[1];return{label:r,options:Object.entries(n).map((function(e){var t=c()(e,2);return{value:t[0],label:t[1]}}))}}));return[].concat(m()(n),[{label:Object(O.__)("Custom"),options:[{value:"custom",label:Object(O.__)("Custom capability")}]}])}),[])};function w(e,t){var r=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),r.push.apply(r,n)}return r}function h(e){for(var t=1;t<arguments.length;t++){var r=null!=arguments[t]?arguments[t]:{};t%2?w(Object(r),!0).forEach((function(t){p()(e,t,r[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(r)):w(Object(r)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(r,t))}))}return e}var g="eventEditor.ticketForm.initalValues",x=function(){var e=Object(o.useTicketsMeta)().getMetaValue,t=j(),r=Object(d.useMemoStringify)(Object(b.getOptionValues)(t));Object(u.useEffect)((function(){return o.hooks.removeFilter(g,y),o.hooks.addFilter(g,y,(function(t,n){var o=e(null===n||void 0===n?void 0:n.id,"capabilityRequired","none"),i="";return r.includes(o)||(i="".concat(o),o="custom"),h(h({},t),{},{capabilityRequired:o,customCapabilityRequired:i})})),function(){return o.hooks.removeFilter(g,y)}}),[e,r])},P=r(4),R=r.n(P),k=r(7),M=r.n(k),S=r(14),E=r.n(S),_=function(e){var t=function(t){var r=t.forwardedRef,n=t.noMargin,o=t.size,i=M()(t,["forwardedRef","noMargin","size"]),c=E()("ee-svg",o&&"ee-icon--".concat(o),n&&"ee-icon--no-margin",i.className);return a.a.createElement(e,R()({},i,{className:c,ref:r}))},r=function(e,r){return a.a.createElement(t,R()({},e,{forwardedRef:r}))};return Object(u.forwardRef)(r)}((function(e){return u.createElement("svg",R()({xmlns:"http://www.w3.org/2000/svg",width:"1.25em",height:"1.25em",viewBox:"0 0 20 20"},e),u.createElement("path",{d:"M8.03 4.46c-.29 1.28.55 3.46 1.97 3.46 1.41 0 2.25-2.18 1.96-3.46-.22-.98-1.08-1.63-1.96-1.63-.89 0-1.74.65-1.97 1.63zm-4.13.9c-.25 1.08.47 2.93 1.67 2.93s1.92-1.85 1.67-2.93c-.19-.83-.92-1.39-1.67-1.39s-1.48.56-1.67 1.39zm8.86 0c-.25 1.08.47 2.93 1.66 2.93 1.2 0 1.92-1.85 1.67-2.93-.19-.83-.92-1.39-1.67-1.39-.74 0-1.47.56-1.66 1.39zm-.59 11.43l1.25-4.3C14.2 10 12.71 8.47 10 8.47c-2.72 0-4.21 1.53-3.44 4.02l1.26 4.3C8.05 17.51 9 18 10 18c.98 0 1.94-.49 2.17-1.21zm-6.1-7.63c-.49.67-.96 1.83-.42 3.59l1.12 3.79c-.34.2-.77.31-1.2.31-.85 0-1.65-.41-1.85-1.03l-1.07-3.65c-.65-2.11.61-3.4 2.92-3.4.27 0 .54.02.79.06-.1.1-.2.22-.29.33zm8.35-.39c2.31 0 3.58 1.29 2.92 3.4l-1.07 3.65c-.2.62-1 1.03-1.85 1.03-.43 0-.86-.11-1.2-.31l1.11-3.77c.55-1.78.08-2.94-.42-3.61-.08-.11-.18-.23-.28-.33.25-.04.51-.06.79-.06z"}))})),q="eventEditor.ticketForm.sections",C=function(){var e=j();Object(u.useEffect)((function(){return o.hooks.addFilter(q,y,(function(t){return[].concat(m()(t),[{name:"wp-users",icon:_,title:Object(O.__)("WP Users"),fields:[{name:"capabilityRequired",label:Object(O.__)("Ticket Capability Requirement"),fieldType:"select",options:e,info:Object(O.__)('It enables you to set restrictions on who can purchase the ticket option. This is an excellent way to create "Member Only" type discounts to people visiting your site.')},{name:"customCapabilityRequired",label:Object(O.__)("Custom Capability"),fieldType:"text",conditions:[{field:"capabilityRequired",compare:"=",value:"custom"}],maxWidth:300}]}])})),function(){return o.hooks.removeFilter(q,y)}}),[])},T=r(15),A=r(8),D="eventEditor.ticket.mutation",F=function(){var e=Object(o.useTicketsMeta)().setMetaValue;Object(u.useEffect)((function(){return o.hooks.addAction(D,y,(function(t,r,n){switch(t){case A.MutationType.Create:case A.MutationType.Update:"string"!==typeof(null===r||void 0===r?void 0:r.capabilityRequired)||Object(T.hasTempId)(n)||e(null===n||void 0===n?void 0:n.id,"capabilityRequired",r.capabilityRequired)}})),function(){return o.hooks.removeAction(D,y)}}),[])};function I(e,t){var r=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),r.push.apply(r,n)}return r}function z(e){for(var t=1;t<arguments.length;t++){var r=null!=arguments[t]?arguments[t]:{};t%2?I(Object(r),!0).forEach((function(t){p()(e,t,r[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(r)):I(Object(r)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(r,t))}))}return e}var U="eventEditor.ticketForm.mutationInput",V=function(){Object(u.useEffect)((function(){return o.hooks.addFilter(U,y,(function(e){var t=e.capabilityRequired,r=e.customCapabilityRequired,n=M()(e,["capabilityRequired","customCapabilityRequired"]);switch(!0){case"string"!==typeof t:return n;case"custom"===t:return z(z({},n),{},{capabilityRequired:r});case"none"===t:return z(z({},n),{},{capabilityRequired:""});default:return z(z({},n),{},{capabilityRequired:t})}})),function(){return o.hooks.removeFilter(U,y)}}),[])},N=function(){x(),C(),V(),F()},W=function(){f(),N()},B=function(){return W(),null};new n.ModalSubscription(o.domain).subscribe((function(e){(0,e.registry.registerContainer)("wp-user-init",B)}))}]);
//# sourceMappingURL=wpUser.4db4fcd5.js.map