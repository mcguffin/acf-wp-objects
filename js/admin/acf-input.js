!function o(a,r,u){function l(e,t){if(!r[e]){if(!a[e]){var i="function"==typeof require&&require;if(!t&&i)return i(e,!0);if(p)return p(e,!0);var n=new Error("Cannot find module '"+e+"'");throw n.code="MODULE_NOT_FOUND",n}var s=r[e]={exports:{}};a[e][0].call(s.exports,function(t){return l(a[e][1][t]||t)},s,s.exports,o,a,r,u)}return r[e].exports}for(var p="function"==typeof require&&require,t=0;t<u.length;t++)l(u[t]);return l}({1:[function(t,e,i){"use strict";t("acf/field-sweet-spot"),t("acf/select-conditions")},{"acf/field-sweet-spot":2,"acf/select-conditions":3}],2:[function(t,e,i){(function(t){"use strict";var e,i=(e="undefined"!=typeof window?window.jQuery:void 0!==t?t.jQuery:null)&&e.__esModule?e:{default:e};var n=acf.Field.extend({type:"image_sweet_spot",events:{'input input[type="range"]':"onChange","change input":"onChange"},$control:function(){return this.$(".acf-input-wrap")},$input:function(){return this.$("input")},$inputX:function(){return this.$('input[type="range"].-sweet-spot-x')},$inputY:function(){return this.$('input[type="range"].-sweet-spot-y')},$inputAltX:function(){return this.$inputX().next('[type="number"]')},$inputAltY:function(){return this.$inputY().next('[type="number"]')},$image:function(){return(0,i.default)([".media-modal img.details-image",".media-modal .thumbnail-image img","#post-body-content .wp_attachment_image img"].join(",")).first()},initialize:function(){console.trace("init"),this.clickedContainer=this.clickedContainer.bind(this),this.setupImage=this.setupImage.bind(this),this.$image().get(0).complete?this.setupImage():this.$image().on("load",this.setupImage)},setupImage:function(){var t=this.$image();this.$markerContainer=(0,i.default)('<div class="sweet-spot-container"></div>').css({left:t.get(0).offsetLeft+"px",top:t.get(0).offsetTop+"px",width:t.width()+"px",height:t.height()+"px"}).insertAfter(t).on("mouseup",this.clickedContainer),this.$marker=(0,i.default)('<span class="sweet-spot-marker"></span>').appendTo(this.$markerContainer),this.setValue({})},clickedContainer:function(t){this.setValue({x:Math.round(100*t.offsetX/this.$markerContainer.width()),y:Math.round(100*t.offsetY/this.$markerContainer.height())})},getValue:function(){return{x:this.$inputX().val(),y:this.$inputY().val()}},setValue:function(t){this.busy=!0,t.x&&(acf.val(this.$inputX(),t.x),acf.val(this.$inputAltX(),this.$inputX().val(),!0)),t.y&&(acf.val(this.$inputY(),t.y),acf.val(this.$inputAltY(),this.$inputY().val(),!0)),this.$marker.css({left:this.$inputX().val()+"%",top:this.$inputY().val()+"%"}),this.busy=!1},onChange:function(t,e){this.busy||(e.get(0)!==this.$inputX().get(0)&&e.get(0)!==this.$inputAltX().get(0)||this.setValue({x:e.val()}),e.get(0)!==this.$inputY().get(0)&&e.get(0)!==this.$inputAltY().get(0)||this.setValue({y:e.val()}))}});acf.registerFieldType(n)}).call(this,"undefined"!=typeof global?global:"undefined"!=typeof self?self:"undefined"!=typeof window?window:{})},{}],3:[function(t,e,i){"use strict";var n=acf.getFieldType("select");["image_size_select","post_type_select","taxonomy_select","role_select"].forEach(function(t){var e=n.extend({type:t});acf.registerFieldType(e),acf.registerConditionForFieldType("hasValue",t),acf.registerConditionForFieldType("hasNoValue",t),acf.registerConditionForFieldType("contains",t),acf.registerConditionForFieldType("selectEqualTo",t),acf.registerConditionForFieldType("selectNotEqualTo",t),acf.registerConditionForFieldType("selectionLessThan",t),acf.registerConditionForFieldType("selectionGreaterThan",t)})},{}]},{},[1]);
//# sourceMappingURL=data:application/json;charset=utf8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbImFkbWluL25vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJhZG1pbi9zcmMvanMvYWRtaW4vYWNmLWlucHV0L2luZGV4LmpzIiwiYWRtaW4vc3JjL2pzL2xpYi9hY2YvZmllbGQtc3dlZXQtc3BvdC5qcyIsImFkbWluL3NyYy9qcy9saWIvYWNmL3NlbGVjdC1jb25kaXRpb25zLmpzIl0sIm5hbWVzIjpbInIiLCJlIiwibiIsInQiLCJvIiwiaSIsImYiLCJjIiwicmVxdWlyZSIsInUiLCJhIiwiRXJyb3IiLCJjb2RlIiwicCIsImV4cG9ydHMiLCJjYWxsIiwibGVuZ3RoIiwiMSIsIm1vZHVsZSIsIl9qcXVlcnkiLCJ3aW5kb3ciLCJnbG9iYWwiLCJTd2VldFNwb3RGaWVsZCIsImFjZiIsIkZpZWxkIiwiZXh0ZW5kIiwidHlwZSIsImV2ZW50cyIsImlucHV0IGlucHV0W3R5cGU9XCJyYW5nZVwiXSIsImNoYW5nZSBpbnB1dCIsIiRjb250cm9sIiwidGhpcyIsIiQiLCIkaW5wdXQiLCIkaW5wdXRYIiwiJGlucHV0WSIsIiRpbnB1dEFsdFgiLCJuZXh0IiwiJGlucHV0QWx0WSIsIiRpbWFnZSIsImRlZmF1bHQiLCJqb2luIiwiZmlyc3QiLCJpbml0aWFsaXplIiwiY29uc29sZSIsInRyYWNlIiwiY2xpY2tlZENvbnRhaW5lciIsImJpbmQiLCJzZXR1cEltYWdlIiwiZ2V0IiwiY29tcGxldGUiLCJvbiIsIiRpbWciLCIkbWFya2VyQ29udGFpbmVyIiwiY3NzIiwibGVmdCIsIm9mZnNldExlZnQiLCJ0b3AiLCJvZmZzZXRUb3AiLCJ3aWR0aCIsImhlaWdodCIsImluc2VydEFmdGVyIiwiJG1hcmtlciIsImFwcGVuZFRvIiwic2V0VmFsdWUiLCJ4IiwiTWF0aCIsInJvdW5kIiwib2Zmc2V0WCIsInkiLCJvZmZzZXRZIiwiZ2V0VmFsdWUiLCJ2YWwiLCJidXN5Iiwib25DaGFuZ2UiLCJldmVudCIsInJlZ2lzdGVyRmllbGRUeXBlIiwic2VsZWN0RmllbGQiLCJnZXRGaWVsZFR5cGUiLCJmb3JFYWNoIiwicmVnaXN0ZXJDb25kaXRpb25Gb3JGaWVsZFR5cGUiXSwibWFwcGluZ3MiOiJDQUFBLFNBQUFBLEVBQUFDLEVBQUFDLEVBQUFDLEdBQUEsU0FBQUMsRUFBQUMsRUFBQUMsR0FBQSxJQUFBSixFQUFBRyxHQUFBLENBQUEsSUFBQUosRUFBQUksR0FBQSxDQUFBLElBQUFFLEVBQUEsbUJBQUFDLFNBQUFBLFFBQUEsSUFBQUYsR0FBQUMsRUFBQSxPQUFBQSxFQUFBRixHQUFBLEdBQUEsR0FBQUksRUFBQSxPQUFBQSxFQUFBSixHQUFBLEdBQUEsSUFBQUssRUFBQSxJQUFBQyxNQUFBLHVCQUFBTixFQUFBLEtBQUEsTUFBQUssRUFBQUUsS0FBQSxtQkFBQUYsRUFBQSxJQUFBRyxFQUFBWCxFQUFBRyxHQUFBLENBQUFTLFFBQUEsSUFBQWIsRUFBQUksR0FBQSxHQUFBVSxLQUFBRixFQUFBQyxRQUFBLFNBQUFkLEdBQUEsT0FBQUksRUFBQUgsRUFBQUksR0FBQSxHQUFBTCxJQUFBQSxJQUFBYSxFQUFBQSxFQUFBQyxRQUFBZCxFQUFBQyxFQUFBQyxFQUFBQyxHQUFBLE9BQUFELEVBQUFHLEdBQUFTLFFBQUEsSUFBQSxJQUFBTCxFQUFBLG1CQUFBRCxTQUFBQSxRQUFBSCxFQUFBLEVBQUFBLEVBQUFGLEVBQUFhLE9BQUFYLElBQUFELEVBQUFELEVBQUFFLElBQUEsT0FBQUQsRUFBQSxDQUFBLENBQUFhLEVBQUEsQ0FBQSxTQUFBVCxFQUFBVSxFQUFBSixnQkNBQU4sRUFBQSx3QkFDQUEsRUFBQSw2SENEQSxNQUFBVyxLQUFBLG9CQUFBQyxPQUFBQSxPQUFBLFlBQUEsSUFBQUMsRUFBQUEsRUFBQSxPQUFBLGtDQVlBLElBQU1DLEVBQWlCQyxJQUFJQyxNQUFNQyxPQUFPLENBRXZDQyxLQUFNLG1CQUVOQyxPQUFRLENBQ1BDLDRCQUE2QixXQUM3QkMsZUFBZ0IsWUFHakJDLFNBQVUsV0FDVCxPQUFPQyxLQUFLQyxFQUFFLG9CQUVmQyxPQUFRLFdBQ1AsT0FB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QUFJcUQsa0JBQW1CekUsR0FFdkJvQixJQUFJeUQsOEJBQStCLFdBQVl0RCxHQUMvQ0gsSUFBSXlELDhCQUErQixhQUFjdEQsR0FDakRILElBQUl5RCw4QkFBK0IsV0FBWXRELEdBQy9DSCxJQUFJeUQsOEJBQStCLGdCQUFpQnRELEdBQ3BESCxJQUFJeUQsOEJBQStCLG1CQUFvQnRELEdBQ3ZESCxJQUFJeUQsOEJBQStCLG9CQUFxQnRELEdBQ3hESCxJQUFJeUQsOEJBQStCLHVCQUF3QnREIiwiZmlsZSI6ImFkbWluL2FjZi1pbnB1dC5qcyIsInNvdXJjZXNDb250ZW50IjpbIihmdW5jdGlvbigpe2Z1bmN0aW9uIHIoZSxuLHQpe2Z1bmN0aW9uIG8oaSxmKXtpZighbltpXSl7aWYoIWVbaV0pe3ZhciBjPVwiZnVuY3Rpb25cIj09dHlwZW9mIHJlcXVpcmUmJnJlcXVpcmU7aWYoIWYmJmMpcmV0dXJuIGMoaSwhMCk7aWYodSlyZXR1cm4gdShpLCEwKTt2YXIgYT1uZXcgRXJyb3IoXCJDYW5ub3QgZmluZCBtb2R1bGUgJ1wiK2krXCInXCIpO3Rocm93IGEuY29kZT1cIk1PRFVMRV9OT1RfRk9VTkRcIixhfXZhciBwPW5baV09e2V4cG9ydHM6e319O2VbaV1bMF0uY2FsbChwLmV4cG9ydHMsZnVuY3Rpb24ocil7dmFyIG49ZVtpXVsxXVtyXTtyZXR1cm4gbyhufHxyKX0scCxwLmV4cG9ydHMscixlLG4sdCl9cmV0dXJuIG5baV0uZXhwb3J0c31mb3IodmFyIHU9XCJmdW5jdGlvblwiPT10eXBlb2YgcmVxdWlyZSYmcmVxdWlyZSxpPTA7aTx0Lmxlbmd0aDtpKyspbyh0W2ldKTtyZXR1cm4gb31yZXR1cm4gcn0pKCkiLCJpbXBvcnQgJ2FjZi9maWVsZC1zd2VldC1zcG90JztcbmltcG9ydCAnYWNmL3NlbGVjdC1jb25kaXRpb25zJztcbiIsImltcG9ydCAkIGZyb20gJ2pxdWVyeSc7XG5cbi8qKlxuICpcdFRlc3QgQ2FzZXNcbiAqXHQtIFt4XSBJbWFnZSBMaWJyYXJ5IG1vZGFsXG4gKlx0LSBbeF0gSW1hZ2UgTGlicmFyeSBzaW5nbGUgdmlld1xuICpcdC0gWyBdIEltYWdlIHNlbGVjdCBtb2RhbFxuICpcdC0gWyBdIEltYWdlIFRodW1ibmFpbCBmaWVsZFxuICpcbiAqXG4gKlxuICovXG5jb25zdCBTd2VldFNwb3RGaWVsZCA9IGFjZi5GaWVsZC5leHRlbmQoe1xuXG5cdHR5cGU6ICdpbWFnZV9zd2VldF9zcG90JyxcblxuXHRldmVudHM6IHtcblx0XHQnaW5wdXQgaW5wdXRbdHlwZT1cInJhbmdlXCJdJzogJ29uQ2hhbmdlJyxcblx0XHQnY2hhbmdlIGlucHV0JzogJ29uQ2hhbmdlJ1xuXHR9LFxuLy8qL1xuXHQkY29udHJvbDogZnVuY3Rpb24oKXtcblx0XHRyZXR1cm4gdGhpcy4kKCcuYWNmLWlucHV0LXdyYXAnKTtcblx0fSxcblx0JGlucHV0OiBmdW5jdGlvbigpe1xuXHRcdHJldHVybiB0aGlzLiQoJ2lucHV0Jyk7XG5cdH0sXG4vLyovXG5cdCRpbnB1dFg6IGZ1bmN0aW9uKCkge1xuXHRcdHJldHVybiB0aGlzLiQoJ2lucHV0W3R5cGU9XCJyYW5nZVwiXS4tc3dlZXQtc3BvdC14Jyk7XG5cdH0sXG5cdCRpbnB1dFk6IGZ1bmN0aW9uKCkge1xuXHRcdHJldHVybiB0aGlzLiQoJ2lucHV0W3R5cGU9XCJyYW5nZVwiXS4tc3dlZXQtc3BvdC15Jyk7XG5cdH0sXG5cdCRpbnB1dEFsdFg6IGZ1bmN0aW9uKCkge1xuXHRcdHJldHVybiB0aGlzLiRpbnB1dFgoKS5uZXh0KCdbdHlwZT1cIm51bWJlclwiXScpO1xuXHR9LFxuXHQkaW5wdXRBbHRZOiBmdW5jdGlvbigpIHtcblx0XHRyZXR1cm4gdGhpcy4kaW5wdXRZKCkubmV4dCgnW3R5cGU9XCJudW1iZXJcIl0nKTtcblx0fSxcblx0JGltYWdlOiBmdW5jdGlvbigpIHtcblx0XHRjb25zdCBzZWxlY3RvcnMgPSBbXG5cdFx0XHQnLm1lZGlhLW1vZGFsIGltZy5kZXRhaWxzLWltYWdlJyxcblx0XHRcdCcubWVkaWEtbW9kYWwgLnRodW1ibmFpbC1pbWFnZSBpbWcnLCAvLyBtZWRpYSBsaWJyYXJ5IG1vZGFsXG5cdFx0XHQnI3Bvc3QtYm9keS1jb250ZW50IC53cF9hdHRhY2htZW50X2ltYWdlIGltZycsIC8vIG1lZGlhIGxpYnJhcnkgc2luZ2xlIGVkaXRcblx0XHRdO1xuXHRcdHJldHVybiAkKCBzZWxlY3RvcnMuam9pbignLCcpICkuZmlyc3QoKTtcblx0fSxcblxuXHRpbml0aWFsaXplOiBmdW5jdGlvbigpIHtcblx0XHRjb25zb2xlLnRyYWNlKCdpbml0Jylcblx0XHR0aGlzLmNsaWNrZWRDb250YWluZXIgPSB0aGlzLmNsaWNrZWRDb250YWluZXIuYmluZCh0aGlzKVxuXHRcdHRoaXMuc2V0dXBJbWFnZSA9IHRoaXMuc2V0dXBJbWFnZS5iaW5kKHRoaXMpO1xuXHRcdGlmICggdGhpcy4kaW1hZ2UoKS5nZXQoMCkuY29tcGxldGUgKSB7XG5cdFx0XHR0aGlzLnNldHVwSW1hZ2UoKTtcblx0XHR9IGVsc2Uge1xuXHRcdFx0dGhpcy4kaW1hZ2UoKS5vbignbG9hZCcsdGhpcy5zZXR1cEltYWdlICk7XG5cdFx0fVxuXHRcdC8vdGhpcy5zZXR1cEltYWdlKClcblx0fSxcblx0c2V0dXBJbWFnZTogZnVuY3Rpb24oKSB7XG5cdFx0bGV0ICRpbWcgPSB0aGlzLiRpbWFnZSgpO1xuXHRcdC8vJGltZy53cmFwKCk7XG5cdFx0Ly8gaWYgKCAkaW1nLnBhcmVudCgpLmNzcygncG9zaXRpb24nKSA9PT0gJ3N0YXRpYycgKSB7XG5cdFx0Ly8gXHQkaW1nLnBhcmVudCgpLmNzcygncG9zaXRpb24nLCAncmVsYXRpdmUnICk7XG5cdFx0Ly8gfVxuXHRcdHRoaXMuJG1hcmtlckNvbnRhaW5lciA9ICQoJzxkaXYgY2xhc3M9XCJzd2VldC1zcG90LWNvbnRhaW5lclwiPjwvZGl2PicpXG5cdFx0XHQuY3NzKHtcblx0XHRcdFx0J2xlZnQnOiRpbWcuZ2V0KDApLm9mZnNldExlZnQgKyAncHgnLFxuXHRcdFx0XHQndG9wJzokaW1nLmdldCgwKS5vZmZzZXRUb3AgKyAncHgnLFxuXHRcdFx0XHQnd2lkdGgnOiRpbWcud2lkdGgoKSArICdweCcsXG5cdFx0XHRcdCdoZWlnaHQnOiRpbWcuaGVpZ2h0KCkgKyAncHgnLFxuXHRcdFx0fSlcblx0XHRcdC5pbnNlcnRBZnRlcigkaW1nKVxuXHRcdFx0Lm9uKCdtb3VzZXVwJyx0aGlzLmNsaWNrZWRDb250YWluZXIgKTtcblx0XHR0aGlzLiRtYXJrZXIgPSAkKCc8c3BhbiBjbGFzcz1cInN3ZWV0LXNwb3QtbWFya2VyXCI+PC9zcGFuPicpLmFwcGVuZFRvKCB0aGlzLiRtYXJrZXJDb250YWluZXIgKTtcblx0XHR0aGlzLnNldFZhbHVlKHt9KVxuXHR9LFxuXHRjbGlja2VkQ29udGFpbmVyOiBmdW5jdGlvbihlKSB7XG5cblx0XHR0aGlzLnNldFZhbHVlKHtcblx0XHRcdHg6IE1hdGgucm91bmQoIDEwMCAqIGUub2Zmc2V0WCAvIHRoaXMuJG1hcmtlckNvbnRhaW5lci53aWR0aCgpICksXG5cdFx0XHR5OiBNYXRoLnJvdW5kKCAxMDAgKiBlLm9mZnNldFkgLyB0aGlzLiRtYXJrZXJDb250YWluZXIuaGVpZ2h0KCkgKSxcblx0XHR9KTtcblxuXHR9LFxuXHRnZXRWYWx1ZTogZnVuY3Rpb24oKSB7XG5cdFx0cmV0dXJuIHtcblx0XHRcdCd4JzogdGhpcy4kaW5wdXRYKCkudmFsKCksXG5cdFx0XHQneSc6IHRoaXMuJGlucHV0WSgpLnZhbCgpLFxuXHRcdH07XG5cdH0sXG5cdHNldFZhbHVlOiBmdW5jdGlvbiggdmFsICl7XG5cdFx0dGhpcy5idXN5ID0gdHJ1ZTtcblxuXHRcdC8vIHVwZGF0ZSBpbnB1dFxuXHRcdGlmICggISEgdmFsLnggKSB7XG5cdFx0XHRhY2YudmFsKCB0aGlzLiRpbnB1dFgoKSwgdmFsLnggKTtcblx0XHRcdGFjZi52YWwoIHRoaXMuJGlucHV0QWx0WCgpLCB0aGlzLiRpbnB1dFgoKS52YWwoKSwgdHJ1ZSApO1xuXHRcdH1cblx0XHRpZiAoICEhIHZhbC55ICkge1xuXHRcdFx0YWNmLnZhbCggdGhpcy4kaW5wdXRZKCksIHZhbC55ICk7XG5cdFx0XHRhY2YudmFsKCB0aGlzLiRpbnB1dEFsdFkoKSwgdGhpcy4kaW5wdXRZKCkudmFsKCksIHRydWUgKTtcblx0XHR9XG5cblx0XHQvLyB1cGRhdGUgaW1hZ2UgbWFya2VyXG5cdFx0dGhpcy4kbWFya2VyLmNzcyh7XG5cdFx0XHQnbGVmdCc6dGhpcy4kaW5wdXRYKCkudmFsKCkgKyAnJScsXG5cdFx0XHQndG9wJzp0aGlzLiRpbnB1dFkoKS52YWwoKSArICclJyxcblx0XHR9KVxuXG5cdFx0dGhpcy5idXN5ID0gZmFsc2U7XG5cdH0sXG5cblx0b25DaGFuZ2U6IGZ1bmN0aW9uKCBldmVudCwgJGlucHV0ICkge1xuXHRcdGlmICggISB0aGlzLmJ1c3kgKSB7XG5cdFx0XHRpZiAoICRpbnB1dC5nZXQoMCkgPT09IHRoaXMuJGlucHV0WCgpLmdldCgwKSB8fCAkaW5wdXQuZ2V0KDApID09PSB0aGlzLiRpbnB1dEFsdFgoKS5nZXQoMCkgKSB7XG5cdFx0XHRcdHRoaXMuc2V0VmFsdWUoIHsgeDogJGlucHV0LnZhbCgpIH0gKVxuXHRcdFx0fVxuXHRcdFx0aWYgKCAkaW5wdXQuZ2V0KDApID09PSB0aGlzLiRpbnB1dFkoKS5nZXQoMCkgfHwgJGlucHV0LmdldCgwKSA9PT0gdGhpcy4kaW5wdXRBbHRZKCkuZ2V0KDApICkge1xuXHRcdFx0XHR0aGlzLnNldFZhbHVlKCB7IHk6ICRpbnB1dC52YWwoKSB9IClcblx0XHRcdH1cblx0XHR9XG5cdH0sXG5cblxufSk7XG5cbmFjZi5yZWdpc3RlckZpZWxkVHlwZShTd2VldFNwb3RGaWVsZClcbiIsImNvbnN0IHNlbGVjdEZpZWxkID0gYWNmLmdldEZpZWxkVHlwZSgnc2VsZWN0Jyk7XG4vLyBtYWtlIHNlbGVjdDIgd29ya1xuW1xuXHQnaW1hZ2Vfc2l6ZV9zZWxlY3QnLFxuXHQncG9zdF90eXBlX3NlbGVjdCcsXG5cdCd0YXhvbm9teV9zZWxlY3QnLFxuXHQncm9sZV9zZWxlY3QnXG5dLmZvckVhY2goXG5cdHR5cGUgPT4ge1xuXG5cdFx0Y29uc3QgdCA9IHNlbGVjdEZpZWxkLmV4dGVuZCgge1xuXHRcdFx0dHlwZSxcblx0XHR9ICk7XG5cdFx0YWNmLnJlZ2lzdGVyRmllbGRUeXBlKCB0ICk7XG5cblx0XHRhY2YucmVnaXN0ZXJDb25kaXRpb25Gb3JGaWVsZFR5cGUoICdoYXNWYWx1ZScsIHR5cGUgKTtcblx0XHRhY2YucmVnaXN0ZXJDb25kaXRpb25Gb3JGaWVsZFR5cGUoICdoYXNOb1ZhbHVlJywgdHlwZSApO1xuXHRcdGFjZi5yZWdpc3RlckNvbmRpdGlvbkZvckZpZWxkVHlwZSggJ2NvbnRhaW5zJywgdHlwZSApO1xuXHRcdGFjZi5yZWdpc3RlckNvbmRpdGlvbkZvckZpZWxkVHlwZSggJ3NlbGVjdEVxdWFsVG8nLCB0eXBlICk7XG5cdFx0YWNmLnJlZ2lzdGVyQ29uZGl0aW9uRm9yRmllbGRUeXBlKCAnc2VsZWN0Tm90RXF1YWxUbycsIHR5cGUgKTtcblx0XHRhY2YucmVnaXN0ZXJDb25kaXRpb25Gb3JGaWVsZFR5cGUoICdzZWxlY3Rpb25MZXNzVGhhbicsIHR5cGUgKTtcblx0XHRhY2YucmVnaXN0ZXJDb25kaXRpb25Gb3JGaWVsZFR5cGUoICdzZWxlY3Rpb25HcmVhdGVyVGhhbicsIHR5cGUgKTtcblxuXHR9XG4pO1xuIl19
