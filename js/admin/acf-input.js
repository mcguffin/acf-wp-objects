!function n(a,o,s){function r(e,t){if(!o[e]){if(!a[e]){var i="function"==typeof require&&require;if(!t&&i)return i(e,!0);if(u)return u(e,!0);throw(i=new Error("Cannot find module '"+e+"'")).code="MODULE_NOT_FOUND",i}i=o[e]={exports:{}},a[e][0].call(i.exports,function(t){return r(a[e][1][t]||t)},i,i.exports,n,a,o,s)}return o[e].exports}for(var u="function"==typeof require&&require,t=0;t<s.length;t++)r(s[t]);return r}({1:[function(t,e,i){"use strict";t("acf/field-sweet-spot"),t("acf/select-conditions"),t("acf/id-field");var n,t=acf.getFieldType("repeater");t&&(n=t.prototype.addSortable,t.prototype.addSortable=function(){if(!this.$el.hasClass("deny-sort")&&!this.$el.hasClass("no-sort"))return n.apply(this,arguments)})},{"acf/field-sweet-spot":2,"acf/id-field":3,"acf/select-conditions":4}],2:[function(t,e,i){!function(n){!function(){"use strict";var t,e=(t="undefined"!=typeof window?window.jQuery:void 0!==n?n.jQuery:null)&&t.__esModule?t:{default:t};var i=acf.Field.extend({type:"image_sweet_spot",events:{'input input[type="range"]':"onChange","change input":"onChange"},$control:function(){return this.$(".acf-input-wrap")},$input:function(){return this.$("input")},$inputX:function(){return this.$('input[type="range"].-sweet-spot-x')},$inputY:function(){return this.$('input[type="range"].-sweet-spot-y')},$inputAltX:function(){return this.$inputX().next('[type="number"]')},$inputAltY:function(){return this.$inputY().next('[type="number"]')},$image:function(){return(0,e.default)([".media-modal img.details-image",".media-modal .thumbnail-image img","#post-body-content .wp_attachment_image img"].join(",")).first()},initialize:function(){this.clickedContainer=this.clickedContainer.bind(this),this.setupImage=this.setupImage.bind(this),this.$marker=!1,this.$markerContainer=!1,this.$image().get(0).complete?this.setupImage():this.$image().on("load",this.setupImage)},setupImage:function(){var t=this.$image();t.length&&(this.$markerContainer=(0,e.default)('<div class="sweet-spot-container"></div>').css({left:t.get(0).offsetLeft+"px",top:t.get(0).offsetTop+"px",width:t.width()+"px",height:t.height()+"px"}).insertAfter(t).on("mouseup",this.clickedContainer),this.$marker=(0,e.default)('<span class="sweet-spot-marker"></span>').appendTo(this.$markerContainer),this.setValue({}))},clickedContainer:function(t){this.setValue({x:Math.round(100*t.offsetX/this.$markerContainer.width()),y:Math.round(100*t.offsetY/this.$markerContainer.height())})},getValue:function(){return{x:this.$inputX().val(),y:this.$inputY().val()}},setValue:function(t){this.busy=!0,t.x&&(acf.val(this.$inputX(),t.x),acf.val(this.$inputAltX(),this.$inputX().val(),!0)),t.y&&(acf.val(this.$inputY(),t.y),acf.val(this.$inputAltY(),this.$inputY().val(),!0)),this.$marker&&this.$marker.css({left:this.$inputX().val()+"%",top:this.$inputY().val()+"%"}),this.busy=!1},onChange:function(t,e){this.busy||(e.get(0)!==this.$inputX().get(0)&&e.get(0)!==this.$inputAltX().get(0)||this.setValue({x:e.val()}),e.get(0)!==this.$inputY().get(0)&&e.get(0)!==this.$inputAltY().get(0)||this.setValue({y:e.val()}))}});acf.registerFieldType(i)}.call(this)}.call(this,"undefined"!=typeof global?global:"undefined"!=typeof self?self:"undefined"!=typeof window?window:{})},{}],3:[function(t,e,i){!function(e){!function(){"use strict";var t,s=(t="undefined"!=typeof window?window.jQuery:void 0!==e?e.jQuery:null)&&t.__esModule?t:{default:t};function r(t){return t.toLowerCase().replace(/\s/g,"-").replace(/[^0-9a-z_\-]/g,"-")}(0,s.default)(document).on("change",'.acf-field.acf-id-field [type="text"]',function(t){var e=(0,s.default)(t.target).closest(".acf-field").attr("data-key"),i=(0,s.default)(t.target).closest(".acf-field").hasClass("acf-id-slug"),n=(0,s.default)(t.target).val(),a=n;i&&(a=a.toLowerCase().normalize("NFD").replace(/(\s+)/g,"-").replace(/[\u0000-\u0020\u007F-\uffff]/g,""));for(var o=0;function(t,e,i){var n=r(e);try{(0,s.default)('[data-key="'.concat(t,'"] [type="text"]')).not(i).each(function(t,e){if(r((0,s.default)(e).val())===n)throw"val exists"})}catch(t){return!0}return!1}(e,a,t.target);)a="".concat(n,i?"-":" ").concat(++o);(0,s.default)(t.target).val(a)}),acf.addAction("duplicate_field",function(t){t.$el.is(".acf-id-field")&&t.$input().prop("readonly",!1).trigger("change")})}.call(this)}.call(this,"undefined"!=typeof global?global:"undefined"!=typeof self?self:"undefined"!=typeof window?window:{})},{}],4:[function(t,e,i){"use strict";var n=acf.getFieldType("select");["image_size_select","post_type_select","taxonomy_select","role_select"].forEach(function(t){var e=n.extend({type:t});acf.registerFieldType(e),acf.registerConditionForFieldType("hasValue",t),acf.registerConditionForFieldType("hasNoValue",t),acf.registerConditionForFieldType("contains",t),acf.registerConditionForFieldType("selectEqualTo",t),acf.registerConditionForFieldType("selectNotEqualTo",t),acf.registerConditionForFieldType("selectionLessThan",t),acf.registerConditionForFieldType("selectionGreaterThan",t)})},{}]},{},[1]);