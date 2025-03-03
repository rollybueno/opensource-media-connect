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
    ToolbarButton,
    Dropdown
} from '@wordpress/components';
import { useState, useRef } from '@wordpress/element';
import { 
    useBlockProps,
    BlockControls,
    InspectorControls
} from '@wordpress/block-editor';
import { 
    image as imageIcon,
    replace,
    cog
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
    const { query, mediaType, license, selectedMedia, showAttribution, imageSize, maxWidth, altText } = attributes;
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
                path: `/openverse-connect/v1/search?q=${encodeURIComponent(query)}&page=${currentPage}&media_type=${mediaType}&license=${license}`,
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
                setError(__('No results found', 'openverse-connect'));
                setSearchResults([]);
            }
        } catch (err) {
            setError(__('Error searching Openverse', 'openverse-connect'));
            console.error('Openverse search error:', err);
        } finally {
            setIsSearching(false);
        }
    };

    const loadMore = () => {
        setPage(page + 1);
        performSearch(false);
    };

    const sanitizeAltText = (text) => {
        const strippedText = text.replace(/<\/?[^>]+(>|$)/g, '');
        return escapeHTML(strippedText);
    };

    const selectMedia = (media) => {
        const defaultAltText = media.title || '';
        setAttributes({ 
            selectedMedia: media,
            altText: altText || sanitizeAltText(defaultAltText)
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
        { label: __('Small', 'openverse-connect'), value: 'small' },
        { label: __('Medium', 'openverse-connect'), value: 'medium' },
        { label: __('Large', 'openverse-connect'), value: 'large' },
    ];

    const renderAttribution = (media) => {
        if (!media) return null;
        
        return (
            <figcaption className="wp-element-caption openverse-attribution">
                    {__('Creator:', 'openverse-connect')} {media.creator || __('Unknown', 'openverse-connect')} | 
                    {__('License:', 'openverse-connect')} {media.license}
            </figcaption>
        );
    };

    const MediaSettingsPanel = () => {
        const [localAltText, setLocalAltText] = useState(altText || '');

        return (
            <>
                <ToggleControl
                    label={__('Show Attribution', 'openverse-connect')}
                    checked={showAttribution}
                    onChange={(value) => {
                        setAttributes({ showAttribution: value });
                    }}
                    __nextHasNoMarginBottom={ true }
                />
                
                {mediaType === 'image' && (
                    <TextControl
                        label={__('Alt Text', 'openverse-connect')}
                        value={localAltText}
                        onChange={(value) => setLocalAltText(value)}
                        onBlur={() => setAttributes({ altText: localAltText })}
                        help={__('Alternative text describes your image to people who can\'t see it. Add a short description with its key details.', 'openverse-connect')}
                        __nextHasNoMarginBottom={ true }
                    />
                )}
            </>
        );
    };

    const renderSelectedMedia = () => {
        if (!selectedMedia) return null;

        return (
            <div className="openverse-selected-media">
                <img 
                    src={selectedMedia.thumbnail || selectedMedia.url} 
                    alt={altText || selectedMedia.title || ''}
                    className={`size-${imageSize}`}
                    style={{ maxWidth: `${maxWidth}%` }}
                />
                
                {showAttribution && renderAttribution(selectedMedia)}
            </div>
        );
    };

    const renderSearchInterface = () => {
        return (
            <div className="openverse-search-interface">
                <Placeholder
                    icon="search"
                    label={__('Openverse Media Search', 'openverse-connect')}
                    instructions={__('Search for free and openly licensed media from Openverse', 'openverse-connect')}
                >
                    <div className="openverse-search-controls">                        
                        <TextControl
                            label={__('Search Query', 'openverse-connect')}
                            hideLabelFromVision={true}
                            value={query}
                            onChange={(value) => setAttributes({ query: value })}
                            placeholder={__('Enter search terms...', 'openverse-connect')}
                            __nextHasNoMarginBottom={ true }
                            __next40pxDefaultSize={ true }
                        />
                        
                        <div className="openverse-search-buttons" style={{ display: 'flex', gap: '8px', marginTop: '10px' }}>
                            <Button
                                variant="primary"
                                onClick={() => performSearch(true)}
                                disabled={!query.trim() || isSearching}
                            >
                                {isSearching ? <Spinner /> : __('Search', 'openverse-connect')}
                            </Button>
                            
                            {previousMediaRef.current && (
                                <Button
                                    variant="secondary"
                                    onClick={cancelSearch}
                                    className="openverse-cancel-search"
                                >
                                    {__('Cancel', 'openverse-connect')}
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
                        <h3>{__('Search Results', 'openverse-connect')}</h3>
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
                                {isSearching ? <Spinner /> : __('Load More', 'openverse-connect')}
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
                            label={__('Replace Media', 'openverse-connect')}
                            onClick={resetSelection}
                        />
                    </ToolbarGroup>
                </BlockControls>
            )}
            
            <InspectorControls>
                <PanelBody title={__('Media Settings', 'openverse-connect')} initialOpen={true}>
                    <MediaSettingsPanel />
                    {mediaType === 'image' && (
                        <>
                            <SelectControl
                                label={__('Image Size', 'openverse-connect')}
                                value={imageSize}
                                options={imageSizeOptions}
                                onChange={(value) => setAttributes({ imageSize: value })}
                                __nextHasNoMarginBottom={ true }
                            />
                            
                            <RangeControl
                                label={__('Maximum Width (%)', 'openverse-connect')}
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