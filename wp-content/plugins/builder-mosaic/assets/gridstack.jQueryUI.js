/** gridstack.js 1.1.2-dev - JQuery UI Drag&Drop*/
(factory=> {
  'use strict';
  factory(jQuery, GridStack, window);
})(($, GridStack, scope)=> {
  'use strict';
  function $Grid(grid) {
    GridStack.DragDropPlugin.call(this, grid);
  }

  GridStack.DragDropPlugin.registerPlugin($Grid);

  $Grid.prototype = Object.create(GridStack.DragDropPlugin.prototype);
  $Grid.prototype.constructor = $Grid;

  $Grid.prototype.resizable = function(el, opts) {
    el = $(el);
    if (opts === 'disable' || opts === 'enable' || opts === 'destroy') {
      el.resizable(opts);
    } else if (opts === 'option') {
      var key = arguments[2],
       value = arguments[3];
      el.resizable(opts, key, value);
    } else {
      var handles = el.data('gs-resize-handles') ? el.data('gs-resize-handles') :
        this.grid.opts.resizable.handles;
      el.resizable($.extend({}, this.grid.opts.resizable, {
        handles: handles
      }, {
        start: opts.start || function() {},
        stop: opts.stop || function() {},
        resize: opts.resize || function() {}
      }));
    }
    return this;
  };

  $Grid.prototype.draggable = function(el, opts) {
    el = $(el);
    if (opts === 'disable' || opts === 'enable' || opts === 'destroy') {
      el.draggable(opts);
    } else {
      el.draggable($.extend({}, this.grid.opts.draggable, {
        containment: (this.grid.opts.isNested && !this.grid.opts.dragOut) ?
          this.grid.$el.parent() :
          (this.grid.opts.draggable.containment || null),
        start: opts.start || function() {},
        stop: opts.stop || function() {},
        drag: opts.drag || function() {}
      }));
    }
    return this;
  };

  $Grid.prototype.droppable = function(el, opts) {
    $(el).droppable(opts);
    return this;
  };

  $Grid.prototype.isDroppable = el=> {
    el = $(el);
    return Boolean(el.data('droppable'));
  };

  $Grid.prototype.on = function(el, eventName, callback) {
    $(el).on(eventName, callback);
    return this;
  };

  scope.$Grid = $Grid;

  return $Grid;
});