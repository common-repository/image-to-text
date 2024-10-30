(()=>{"use strict";var e={n:t=>{var o=t&&t.__esModule?()=>t.default:()=>t;return e.d(o,{a:o}),o},d:(t,o)=>{for(var l in o)e.o(o,l)&&!e.o(t,l)&&Object.defineProperty(t,l,{enumerable:!0,get:o[l]})},o:(e,t)=>Object.prototype.hasOwnProperty.call(e,t)};const t=window.wp.blocks,o=window.React,l=window.wp.i18n,a=window.wp.serverSideRender;var n=e.n(a);const i=window.wp.components,r=window.wp.blockEditor,c=JSON.parse('{"u2":"image-to-text/imagetotext-block"}');(0,t.registerBlockType)(c.u2,{edit:function({attributes:e,setAttributes:t}){let a=[];for(let e=0;e<imagetotext_file.length;e++)a.push({label:imagetotext_file[e],value:imagetotext_file[e]});const c=(0,r.useBlockProps)();return(0,o.createElement)("div",{...c},(0,o.createElement)(n(),{block:"image-to-text/imagetotext-block",attributes:e}),(0,o.createElement)(i.TextControl,{label:(0,l.__)("Text","image-to-text"),value:e.text,onChange:e=>t({text:e})}),(0,o.createElement)(r.InspectorControls,null,(0,o.createElement)(i.PanelBody,{title:(0,l.__)("View","image-to-text"),initialOpen:!1},(0,o.createElement)(i.TextControl,{label:(0,l.__)("Text","image-to-text"),value:e.text,onChange:e=>t({text:e})}),(0,o.createElement)(i.ToggleControl,{label:(0,l.__)("alt text","image-to-text"),checked:e.alt,onChange:e=>t({alt:e})}),(0,o.createElement)(i.TextControl,{label:(0,l.__)("alt text","image-to-text"),value:e.alt_text,onChange:e=>t({alt_text:e})}),(0,o.createElement)(r.PanelColorSettings,{title:(0,l.__)("Color Settings","image-to-text"),colorSettings:[{value:e.back_color,onChange:e=>t({back_color:e}),label:(0,l.__)("Back Color","image-to-text")},{value:e.font_color,onChange:e=>t({font_color:e}),label:(0,l.__)("Font Color","image-to-text")}]})),(0,o.createElement)(i.PanelBody,{title:(0,l.__)("Font","image-to-text"),initialOpen:!1},(0,o.createElement)(i.RangeControl,{label:(0,l.__)("Font Size","image-to-text"),max:100,min:3,value:e.font_size,onChange:e=>t({font_size:e})}),(0,o.createElement)(i.SelectControl,{label:(0,l.__)("Font file","image-to-text"),value:e.font_file,options:a,onChange:e=>{t({font_file:e})}}),(0,o.createElement)(i.RangeControl,{label:(0,l.__)("Font baseline adjust","image-to-text"),max:1.45,min:1.2,step:.01,value:e.vt_mg,onChange:e=>t({vt_mg:e})}))))}})})();