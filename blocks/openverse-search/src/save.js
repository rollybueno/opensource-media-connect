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
        imageSize, 
        maxWidth,
        altText,
        imageCaption
    } = attributes;

    if (!selectedMedia) {
        return <div {...blockProps}></div>;
    }

    const renderCaptionAttribution = () => {
        if (!showAttribution && !imageCaption) return null;
        
        return (
            <figcaption className="wp-element-caption openverse-attribution">
                {imageCaption && (
                    <>
                        {imageCaption}
                    </>
                )}
                {showAttribution && (
                    <>
                        <br />
                        Creator: {selectedMedia.creator || 'Unknown'} | 
                        License: {selectedMedia.license}
                    </>
                )}
            </figcaption>
        )
    };

    return (
        <figure {...blockProps}>
            <img 
                src={selectedMedia.url} 
                alt={altText || selectedMedia.title || ''}
                className={`size-${imageSize}`}
                style={{ maxWidth: `${maxWidth}%` }}
            />
            
            {renderCaptionAttribution()}
        </figure>
    );
} 