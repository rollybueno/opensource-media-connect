/**
 * WordPress dependencies
 */
import { useBlockProps } from '@wordpress/block-editor';

/**
 * Save component for the Openverse Search block.
 *
 * @param {Object} props Block props.
 * @return {JSX.Element} Block save component.
 */
export default function Save({ attributes }) {
    const blockProps = useBlockProps.save();
    const { 
        selectedMedia, 
        showAttribution, 
        mediaType, 
        imageSize, 
        maxWidth,
        altText 
    } = attributes;

    if (!selectedMedia) {
        return <div {...blockProps}></div>;
    }

    const renderAttribution = () => {
        if (!showAttribution) return null;
        
        return (
            <figcaption className="wp-element-caption openverse-attribution">
                    Creator: {selectedMedia.creator || 'Unknown'} | 
                    License: {selectedMedia.license}
            </figcaption>
        );
    };

    return (
        <figure {...blockProps}>
            {mediaType === 'image' ? (
                <img 
                    src={selectedMedia.url} 
                    alt={altText || selectedMedia.title || ''}
                    className={`size-${imageSize}`}
                    style={{ maxWidth: `${maxWidth}%` }}
                />
            ) : (
                <audio controls>
                    <source src={selectedMedia.url} type="audio/mpeg" />
                    Your browser does not support the audio element.
                </audio>
            )}
            
            {renderAttribution()}
        </figure>
    );
} 