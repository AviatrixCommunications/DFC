wp.domReady(() => {
    // Adjust Core Button Block Options
    wp.blocks.unregisterBlockStyle('core/button', 'outline');
    wp.blocks.unregisterBlockStyle('core/button', 'fill');
  
    wp.blocks.registerBlockStyle('core/button', {
      name: 'primary-button',
      label: 'Default (Primary)',
      isDefault: true
    });
  
    wp.blocks.registerBlockStyle('core/button', {
      name: 'secondary-button',
      label: 'Secondary'
    });
  
    wp.blocks.registerBlockStyle('core/button', {
      name: 'text-button',
      label: 'Text'
    });
});