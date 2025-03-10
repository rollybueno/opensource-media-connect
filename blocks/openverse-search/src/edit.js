/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { 
    TextControl, 
    Button, 
    Spinner,
    PanelBody,
    SelectControl,
    Placeholder,
    ToggleControl,
    RangeControl,
    ToolbarGroup,
    ToolbarButton
} from '@wordpress/components';
import { useState, useRef } from '@wordpress/element';
import { 
    useBlockProps,
    BlockControls,
    InspectorControls
} from '@wordpress/block-editor';
import { 
    image as imageIcon,
    replace
} from '@wordpress/icons';
import apiFetch from '@wordpress/api-fetch';
import { escapeHTML } from '@wordpress/escape-html';

/**
 * Edit component for the Openverse Search block.
 *
 * @param {Object} props Block props.
 * @return {JSX.Element} Block edit component.
 */
export default function Edit({ attributes, setAttributes }) {
    const blockProps = useBlockProps();
    const { query, mediaType, license, selectedMedia, showAttribution, imageSize, maxWidth, altText, imageCaption } = attributes;
    const [searchResults, setSearchResults] = useState([]);
    const [isSearching, setIsSearching] = useState(false);
    const [error, setError] = useState(null);
    const [page, setPage] = useState(1);
    const [hasMore, setHasMore] = useState(false);
    const [isSearchInterfaceOpen, setIsSearchInterfaceOpen] = useState(false);
    const previousMediaRef = useRef(null);

    const performSearch = async (resetResults = true) => {
        if (!query.trim()) {
            return;
        }

        setIsSearching(true);
        setError(null);
        
        try {
            const currentPage = resetResults ? 1 : page;
            const response = await apiFetch({
                path: `/opensource-media-connect/v1/search?q=${encodeURIComponent(query)}&page=${currentPage}&media_type=${mediaType}&license=${license}`,
                method: 'GET',
            });
            
            if (response.results) {
                if (resetResults) {
                    setSearchResults(response.results);
                    setPage(1);
                } else {
                    setSearchResults([...searchResults, ...response.results]);
                }
                
                setHasMore(response.page_count > currentPage);
            } else {
                setError(__('No results found', 'opensource-media-connect'));
                setSearchResults([]);
            }
        } catch (err) {
            setError(__('Error searching Openverse', 'opensource-media-connect'));
            console.error('Openverse search error:', err);
        } finally {
            setIsSearching(false);
        }
    };

    const loadMore = () => {
        setPage(page + 1);
        performSearch(false);
    };

    const selectMedia = (media) => {
        const defaultAltText = media.title || '';
        const defaultCaption = media.caption || '';
        setAttributes({ 
            selectedMedia: media,
            altText: altText || escapeHTML(defaultAltText),
            imageCaption: defaultCaption || escapeHTML(defaultCaption)
        });
    };

    const openSearchInterface = () => {
        previousMediaRef.current = selectedMedia;
        setIsSearchInterfaceOpen(true);
    };

    const cancelSearch = () => {
        setAttributes({ 
            query: '', 
            mediaType: 'image', 
            license: 'all' 
        });
        
        setSearchResults([]);
        
        setIsSearchInterfaceOpen(false);
        
        if (previousMediaRef.current) {
            setAttributes({ selectedMedia: previousMediaRef.current });
        }
    };

    const resetSelection = () => {
        setAttributes({ selectedMedia: null });
        openSearchInterface();
    };

    const imageSizeOptions = [
        { label: __('Small', 'opensource-media-connect'), value: 'small' },
        { label: __('Medium', 'opensource-media-connect'), value: 'medium' },
        { label: __('Large', 'opensource-media-connect'), value: 'large' },
    ];

    const renderAttribution = (media) => {
        if (!media) return null;
        
        return (
            <figcaption className="wp-element-caption openverse-attribution">
                    {__('Creator:', 'opensource-media-connect')} {media.creator || __('Unknown', 'opensource-media-connect')} | 
                    {__('License:', 'opensource-media-connect')} {media.license}
            </figcaption>
        );
    };

    const MediaSettingsPanel = () => {
        const [localAltText, setLocalAltText] = useState(altText || '');
        const [localCaption, setLocalCaption] = useState(selectedMedia?.caption || '');

        return (
            <>
                <ToggleControl
                    label={__('Show Attribution', 'opensource-media-connect')}
                    checked={showAttribution}
                    onChange={(value) => {
                        setAttributes({ showAttribution: value });
                    }}
                    __nextHasNoMarginBottom={ true }
                />
                
                {mediaType === 'image' && (
                    <>
                        <TextControl
                            label={__('Caption', 'opensource-media-connect')}
                            value={localCaption}
                            onChange={(value) => setLocalCaption(value)}
                            onBlur={() => {
                                if (selectedMedia) {
                                    setAttributes({ 
                                        selectedMedia: {
                                            ...selectedMedia,
                                            caption: escapeHTML(localCaption)
                                        },
                                        imageCaption: escapeHTML(localCaption)
                                    });
                                }
                            }}
                            help={__('Add a caption to display below the image.', 'opensource-media-connect')}
                            __nextHasNoMarginBottom={ true }
                        />
                
                        <TextControl
                            label={__('Alt Text', 'opensource-media-connect')}
                            value={localAltText}
                            onChange={(value) => setLocalAltText(value)}
                            onBlur={() => setAttributes({ altText: escapeHTML(localAltText) })}
                            help={__('Alternative text describes your image to people who can\'t see it. Add a short description with its key details.', 'opensource-media-connect')}
                            __nextHasNoMarginBottom={ true }
                        />
                        
                    </>
                )}
            </>
        );
    };

    const renderSelectedMedia = () => {
        if (!selectedMedia) return null;

        return (
            <div className="openverse-selected-media">
                <figure>
                    <img 
                        src={selectedMedia.thumbnail || selectedMedia.url} 
                        alt={altText || selectedMedia.title || ''}
                        className={`size-${imageSize}`}
                        style={{ maxWidth: `${maxWidth}%` }}
                    />
                    {selectedMedia.caption && (
                        <figcaption className="wp-element-caption">
                            {selectedMedia.caption}
                        </figcaption>
                    )}
                    {showAttribution && renderAttribution(selectedMedia)}
                </figure>
            </div>
        );
    };

    const renderSearchInterface = () => {
        return (
            <div className="openverse-search-interface">
                <Placeholder
                    icon="search"
                    label={__('Open Source Media Search', 'opensource-media-connect')}
                    instructions={__('Search for free and openly licensed media from Openverse', 'opensource-media-connect')}
                >
                    <div className="openverse-search-controls">                        
                        <TextControl
                            label={__('Search Query', 'opensource-media-connect')}
                            hideLabelFromVision={true}
                            value={query}
                            onChange={(value) => setAttributes({ query: value })}
                            placeholder={__('Enter search terms...', 'opensource-media-connect')}
                            __nextHasNoMarginBottom={ true }
                            __next40pxDefaultSize={ true }
                        />
                        
                        <div className="openverse-search-buttons" style={{ display: 'flex', gap: '8px', marginTop: '10px' }}>
                            <Button
                                variant="primary"
                                onClick={() => performSearch(true)}
                                disabled={!query.trim() || isSearching}
                            >
                                {isSearching ? <Spinner /> : __('Search', 'opensource-media-connect')}
                            </Button>
                            
                            {previousMediaRef.current && (
                                <Button
                                    variant="secondary"
                                    onClick={cancelSearch}
                                    className="openverse-cancel-search"
                                >
                                    {__('Cancel', 'opensource-media-connect')}
                                </Button>
                            )}
                        </div>
                    </div>
                </Placeholder>

                {error && (
                    <div className="openverse-error-message">
                        {error}
                    </div>
                )}

                {searchResults.length > 0 && (
                    <div className="openverse-search-results">
                        <h3>{__('Search Results', 'opensource-media-connect')}</h3>
                        <div className="openverse-results-grid">
                            {searchResults.map((item) => (
                                <div 
                                    key={item.id} 
                                    className="openverse-result-item"
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        selectMedia(item);
                                        setIsSearchInterfaceOpen(false);
                                    }}
                                    role="button"
                                    tabIndex={0}
                                    onKeyDown={(e) => {
                                        if (e.key === 'Enter' || e.key === ' ') {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            selectMedia(item);
                                            setIsSearchInterfaceOpen(false);
                                        }
                                    }}
                                >
                                    <img 
                                        src={item.thumbnail} 
                                        alt={item.title} 
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            selectMedia(item);
                                            setIsSearchInterfaceOpen(false);
                                        }}
                                    />
                                </div>
                            ))}
                        </div>
                        
                        {hasMore && (
                            <Button
                                onClick={loadMore}
                                disabled={isSearching}
                                className="openverse-load-more button button-secondary"
                            >
                                {isSearching ? <Spinner /> : __('Load More', 'opensource-media-connect')}
                            </Button>
                        )}
                    </div>
                )}
            </div>
        );
    };

    return (
        <>
            {selectedMedia && !isSearchInterfaceOpen && (
                <BlockControls>
                    <ToolbarGroup>
                        <ToolbarButton
                            icon={replace}
                            label={__('Replace Media', 'opensource-media-connect')}
                            onClick={resetSelection}
                        />
                    </ToolbarGroup>
                </BlockControls>
            )}
            
            <InspectorControls>
                <PanelBody title={__('Media Settings', 'opensource-media-connect')} initialOpen={true}>
                    <MediaSettingsPanel />
                    {mediaType === 'image' && (
                        <>
                            <SelectControl
                                label={__('Image Size', 'opensource-media-connect')}
                                value={imageSize}
                                options={imageSizeOptions}
                                onChange={(value) => setAttributes({ imageSize: value })}
                                __nextHasNoMarginBottom={ true }
                            />
                            
                            <RangeControl
                                label={__('Maximum Width (%)', 'opensource-media-connect')}
                                value={maxWidth}
                                onChange={(value) => setAttributes({ maxWidth: value })}
                                min={10}
                                max={100}
                                __nextHasNoMarginBottom={ true }
                            />
                        </>
                    )}
                </PanelBody>
            </InspectorControls>
            
            <div {...blockProps}>
                {selectedMedia && !isSearchInterfaceOpen ? (
                    renderSelectedMedia()
                ) : (
                    renderSearchInterface()
                )}
            </div>
        </>
    );
} 