"use strict";(self.webpackChunkblockbite=self.webpackChunkblockbite||[]).push([[420],{9420:function(e,t,n){var a=this&&this.__createBinding||(Object.create?function(e,t,n,a){void 0===a&&(a=n);var l=Object.getOwnPropertyDescriptor(t,n);l&&!("get"in l?!t.__esModule:l.writable||l.configurable)||(l={enumerable:!0,get:function(){return t[n]}}),Object.defineProperty(e,a,l)}:function(e,t,n,a){void 0===a&&(a=n),e[a]=t[n]}),l=this&&this.__setModuleDefault||(Object.create?function(e,t){Object.defineProperty(e,"default",{enumerable:!0,value:t})}:function(e,t){e.default=t}),o=this&&this.__importStar||function(e){if(e&&e.__esModule)return e;var t={};if(null!=e)for(var n in e)"default"!==n&&Object.prototype.hasOwnProperty.call(e,n)&&a(t,e,n);return l(t,e),t},r=this&&this.__importDefault||function(e){return e&&e.__esModule?e:{default:e}};Object.defineProperty(t,"__esModule",{value:!0}),t.ButtonGroupRange=void 0;const u=o(n(1609)),i=r(n(7162)),c=n(7162),f=r(n(7124)),s=r(n(6104)),d=n(9795);t.ButtonGroupRange=({options:e,activeOptionValue:t,emitOptions:n,modifierId:a})=>{const[l,o]=(0,u.useState)(""),[r,p]=(0,u.useState)({min:0,max:0}),[g,h]=(0,u.useState)("off");return(0,u.useEffect)((()=>{e.length&&p({min:e[0].value,max:e[e.length-1].value});const{foundValue:n}=(0,d.getValueUnit)(t);o(n)}),[t]),console.log("ButtonGroupRange"),u.default.createElement("div",null,u.default.createElement(c.ButtonToggle,{value:g,icon:s.default,defaultPressed:g,onPressedChange:e=>{h("on"===e?"off":"on")}},"Toggle"),"on"===g&&u.default.createElement(i.default,{size:"small",defaultPressed:l,options:e.map((e=>({label:e.label,value:e.value.toString(),icon:null==e?void 0:e.icon}))),onPressedChange:e=>{o(e),n([{id:a,value:e}])}}),u.default.createElement(f.default,{defaultValue:l,label:"",min:r.min,max:r.max,showTooltip:!0,onValueChange:e=>{let t=(0,d.formatUnit)(e,"arbitrary","");"opacity"===a?t=(0,d.formatUnit)(parseInt(e)/100,"arbitrary",""):"gridcols"!==a&&"gridrows"!==a||(t=e),o(e),n([{id:a,value:t}])}}))},t.default=t.ButtonGroupRange},7124:function(e,t,n){var a=this&&this.__createBinding||(Object.create?function(e,t,n,a){void 0===a&&(a=n);var l=Object.getOwnPropertyDescriptor(t,n);l&&!("get"in l?!t.__esModule:l.writable||l.configurable)||(l={enumerable:!0,get:function(){return t[n]}}),Object.defineProperty(e,a,l)}:function(e,t,n,a){void 0===a&&(a=n),e[a]=t[n]}),l=this&&this.__setModuleDefault||(Object.create?function(e,t){Object.defineProperty(e,"default",{enumerable:!0,value:t})}:function(e,t){e.default=t}),o=this&&this.__importStar||function(e){if(e&&e.__esModule)return e;var t={};if(null!=e)for(var n in e)"default"!==n&&Object.prototype.hasOwnProperty.call(e,n)&&a(t,e,n);return l(t,e),t};Object.defineProperty(t,"__esModule",{value:!0});const r=o(n(1609)),u=n(6427);t.default=({defaultValue:e,label:t,min:n=0,max:a=2e3,withInputField:l=!1,onValueChange:o,gridMode:i=!1,showTooltip:c=!1})=>{const[f]=(0,r.useState)(0),[s,d]=(0,r.useState)(0);return(0,r.useEffect)((()=>{d(Math.round(parseInt(e)/(i?16:1)))}),[e]),r.default.createElement("div",{className:"flex min-w-[240px] flex-col"},r.default.createElement(u.RangeControl,{label:t,value:s,min:n,max:a,showTooltip:c,withInputField:l,onChange:e=>{d(e),function(e){o((e*(i?16:1)).toString())}(e)},resetFallbackValue:f,allowReset:!0}),r.default.createElement("span",null,i?16*s+"px":null," "))}},6104:(e,t,n)=>{n.r(t),n.d(t,{default:()=>o});var a=n(1609);function l(){return l=Object.assign?Object.assign.bind():function(e){for(var t=1;t<arguments.length;t++){var n=arguments[t];for(var a in n)({}).hasOwnProperty.call(n,a)&&(e[a]=n[a])}return e},l.apply(null,arguments)}const o=function(e){return a.createElement("svg",l({width:e.width||"1em",height:e.height||"1em",viewBox:"0 0 15 15",fill:"none",xmlns:"http://www.w3.org/2000/svg",role:"img"},e),a.createElement("path",{d:"M6.81831 4.18185L9.81831 7.18185C9.9027 7.26624 9.95011 7.3807 9.95011 7.50005C9.95011 7.6194 9.9027 7.73386 9.81831 7.81825L6.81831 10.8182C6.64257 10.994 6.35765 10.994 6.18191 10.8182C6.00618 10.6425 6.00618 10.3576 6.18191 10.1819L8.86371 7.50005L6.18191 4.81825C6.00618 4.64251 6.00618 4.35759 6.18191 4.18185C6.35765 4.00611 6.64257 4.00611 6.81831 4.18185Z",fill:"currentColor"}))}}}]);