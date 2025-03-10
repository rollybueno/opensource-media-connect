/**
 * Openverse Search Block
 *
 * Allows users to search and insert media from Openverse directly in the editor.
 */

import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import Edit from './edit';
import Save from './save';
import './style.scss';
import './index.scss';

// Import the block.json file to ensure it's included in the build
import '../block.json';

// Register the block
registerBlockType('openverse-connect/search', {
    title: __('Open Source Media', 'opensource-media-connect'),
    description: __('Search and insert media from Openverse', 'opensource-media-connect'),
    icon: 'format-image',
    category: 'media',
    keywords: [
        __('openverse', 'opensource-media-connect'),
        __('image', 'opensource-media-connect'),
        __('search', 'opensource-media-connect'),
    ],
    edit: Edit,
    save: Save,
}); 