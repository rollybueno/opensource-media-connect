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
    const { selectedMedia, showAttribution, mediaType, imageSize, maxWidth } = attributes;

    if (!selectedMedia) {
        return <div {...blockProps}></div>;
    }

    const renderAttribution = () => {
        if (!showAttribution) return null;
        
        return (
            <div className="openverse-attribution">
                <small>
                    Creator: {selectedMedia.creator || 'Unknown'} | 
                    License: {selectedMedia.license}
                </small>
            </div>
        );
    };

    return (
        <div {...blockProps} style={{ maxWidth: `${maxWidth}%` }}>
            {mediaType === 'image' ? (
                <img 
                    src={selectedMedia.url} 
                    alt={selectedMedia.title}
                    className={`size-${imageSize}`}
                />
            ) : (
                <audio controls>
                    <source src={selectedMedia.url} type="audio/mpeg" />
                    Your browser does not support the audio element.
                </audio>
            )}
            
            {renderAttribution()}
        </div>
    );
} 