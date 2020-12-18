import 'acf/field-sweet-spot';
import 'acf/select-conditions';
import 'acf/id-field';

// repeater field deny sorting
const RepeaterField = acf.getFieldType('repeater')

// wrap around original addSortable function
const origSortable = RepeaterField.prototype.addSortable
RepeaterField.prototype.addSortable = function() {
	if ( this.$el.hasClass( 'deny-sort') || this.$el.hasClass( 'no-sort') ) {
		return;
	}
	return origSortable.apply( this, arguments );
}
