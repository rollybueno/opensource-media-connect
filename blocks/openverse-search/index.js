/**
 * Openverse Search Block
 *
 * Allows users to search and insert media from Openverse directly in the editor.
 */

import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import {
    TextControl,
    Button,
    Spinner,
    Panel,
    PanelBody,
    SelectControl,
    Placeholder,
    ToggleControl,
    RangeControl
} from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { useBlockProps } from '@wordpress/block-editor';
import apiFetch from '@wordpress/api-fetch';

// Register the block
registerBlockType('openverse-connect/search', {
    title: __('Openverse Search', 'openverse-connect'),
    description: __('Search and insert media from Openverse', 'openverse-connect'),
    icon: 'search',
    category: 'media',
    keywords: [
        __('openverse', 'openverse-connect'),
        __('image', 'openverse-connect'),
        __('search', 'openverse-connect'),
    ],
    attributes: {
        searchQuery: {
            type: 'string',
            default: '',
        },
        mediaType: {
            type: 'string',
            default: 'image',
        },
        selectedMedia: {
            type: 'object',
            default: null,
        },
        showAttribution: {
            type: 'boolean',
            default: true,
        },
        imageSize: {
            type: 'string',
            default: 'medium',
        },
        maxWidth: {
            type: 'number',
            default: 100,
        }
    },

    edit: ({ attributes, setAttributes }) => {
        const blockProps = useBlockProps();
        const { searchQuery, mediaType, selectedMedia, showAttribution, imageSize, maxWidth } = attributes;
        const [searchResults, setSearchResults] = useState([]);
        const [isSearching, setIsSearching] = useState(false);
        const [error, setError] = useState(null);
        const [page, setPage] = useState(1);
        const [hasMore, setHasMore] = useState(false);

        const performSearch = async (query, page = 1, mediaType = 'image') => {
            if (!query) return;

            setIsSearching(true);
            setError(null);

            try {
                const response = await apiFetch({
                    path: `/openverse-connect/v1/search?q=${encodeURIComponent(query)}&page=${page}&media_type=${mediaType}`,
                    method: 'GET',
                });

                if (response.results) {
                    if (page === 1) {
                        setSearchResults(response.results);
                    } else {
                        setSearchResults([...searchResults, ...response.results]);
                    }
                    setHasMore(response.page_count > page);
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

        const handleSearch = () => {
            setPage(1);
            performSearch(searchQuery, 1, mediaType);
        };

        const loadMore = () => {
            const nextPage = page + 1;
            setPage(nextPage);
            performSearch(searchQuery, nextPage, mediaType);
        };

        const selectMedia = (media) => {
            setAttributes({ selectedMedia: media });
        };

        const renderAttribution = (media) => {
            if (!media || !showAttribution) return null;

            return (
                <div className="openverse-attribution">
                    <small>
                        {__('Creator:', 'openverse-connect')} {media.creator || __('Unknown', 'openverse-connect')} |
                        {__('License:', 'openverse-connect')} <a href={media.license_url} target="_blank" rel="noopener noreferrer">{media.license}</a>
                    </small>
                </div>
            );
        };

        const renderSelectedMedia = () => {
            if (!selectedMedia) return null;

            return (
                <div className="openverse-selected-media" style={{ maxWidth: `${maxWidth}%` }}>
                    {mediaType === 'image' && (
                        <img
                            src={selectedMedia[`${imageSize}_url`] || selectedMedia.url}
                            alt={selectedMedia.title}
                            style={{ width: '100%', height: 'auto' }}
                        />
                    )}
                    {mediaType === 'audio' && (
                        <audio controls src={selectedMedia.url}>
                            {__('Your browser does not support the audio element.', 'openverse-connect')}
                        </audio>
                    )}
                    {renderAttribution(selectedMedia)}
                </div>
            );
        };

        const renderSearchResults = () => {
            if (isSearching && page === 1) {
                return <Spinner />;
            }

            if (error) {
                return <p className="openverse-error">{error}</p>;
            }

            if (searchResults.length === 0) {
                return <p>{__('No results found. Try a different search.', 'openverse-connect')}</p>;
            }

            return (
                <div className="openverse-results-grid">
                    {searchResults.map((item) => (
                        <div
                            key={item.id}
                            className="openverse-result-item"
                            onClick={() => selectMedia(item)}
                        >
                            {mediaType === 'image' && (
                                <img src={item.thumbnail} alt={item.title} />
                            )}
                            {mediaType === 'audio' && (
                                <div className="openverse-audio-item">
                                    <span className="dashicons dashicons-format-audio"></span>
                                    <span className="openverse-audio-title">{item.title}</span>
                                </div>
                            )}
                        </div>
                    ))}

                    {hasMore && (
                        <div className="openverse-load-more">
                            <Button
                                isPrimary
                                onClick={loadMore}
                                disabled={isSearching}
                            >
                                {isSearching ? <Spinner /> : __('Load More', 'openverse-connect')}
                            </Button>
                        </div>
                    )}
                </div>
            );
        };

        return (
            <div {...blockProps}>
                {selectedMedia ? (
                    <>
                        {renderSelectedMedia()}
                        <Button
                            isSecondary
                            onClick={() => setAttributes({ selectedMedia: null })}
                        >
                            {__('Change Media', 'openverse-connect')}
                        </Button>

                        <Panel>
                            <PanelBody title={__('Media Settings', 'openverse-connect')} initialOpen={false}>
                                <ToggleControl
                                    label={__('Show Attribution', 'openverse-connect')}
                                    checked={showAttribution}
                                    onChange={(value) => setAttributes({ showAttribution: value })}
                                />

                                {mediaType === 'image' && (
                                    <>
                                        <SelectControl
                                            label={__('Image Size', 'openverse-connect')}
                                            value={imageSize}
                                            options={[
                                                { label: __('Small', 'openverse-connect'), value: 'small' },
                                                { label: __('Medium', 'openverse-connect'), value: 'medium' },
                                                { label: __('Large', 'openverse-connect'), value: 'large' },
                                            ]}
                                            onChange={(value) => setAttributes({ imageSize: value })}
                                        />

                                        <RangeControl
                                            label={__('Max Width (%)', 'openverse-connect')}
                                            value={maxWidth}
                                            onChange={(value) => setAttributes({ maxWidth: value })}
                                            min={10}
                                            max={100}
                                        />
                                    </>
                                )}
                            </PanelBody>
                        </Panel>
                    </>
                ) : (
                    <Placeholder
                        icon="search"
                        label={__('Openverse Search', 'openverse-connect')}
                        instructions={__('Search for free media from Openverse to add to your content.', 'openverse-connect')}
                    >
                        <div className="openverse-search-controls">
                            <SelectControl
                                label={__('Media Type', 'openverse-connect')}
                                value={mediaType}
                                options={[
                                    { label: __('Images', 'openverse-connect'), value: 'image' },
                                    { label: __('Audio', 'openverse-connect'), value: 'audio' },
                                ]}
                                onChange={(value) => setAttributes({ mediaType: value })}
                            />

                            <TextControl
                                label={__('Search Query', 'openverse-connect')}
                                value={searchQuery}
                                onChange={(value) => setAttributes({ searchQuery: value })}
                                onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                            />

                            <Button
                                isPrimary
                                onClick={handleSearch}
                                disabled={!searchQuery || isSearching}
                            >
                                {__('Search', 'openverse-connect')}
                            </Button>
                        </div>

                        <div className="openverse-search-results">
                            {renderSearchResults()}
                        </div>
                    </Placeholder>
                )}
            </div>
        );
    },

    save: ({ attributes }) => {
        const { selectedMedia, showAttribution, mediaType, imageSize, maxWidth } = attributes;
        const blockProps = useBlockProps.save();

        if (!selectedMedia) {
            return null;
        }

        return (
            <div {...blockProps} style={{ maxWidth: `${maxWidth}%` }}>
                {mediaType === 'image' && (
                    <img
                        src={selectedMedia[`${imageSize}_url`] || selectedMedia.url}
                        alt={selectedMedia.title}
                        style={{ width: '100%', height: 'auto' }}
                    />
                )}

                {mediaType === 'audio' && (
                    <audio controls src={selectedMedia.url}>
                        {__('Your browser does not support the audio element.', 'openverse-connect')}
                    </audio>
                )}

                {showAttribution && (
                    <div className="openverse-attribution">
                        <small>
                            {__('Creator:', 'openverse-connect')} {selectedMedia.creator || __('Unknown', 'openverse-connect')} |
                            {__('License:', 'openverse-connect')} <a href={selectedMedia.license_url} target="_blank" rel="noopener noreferrer">{selectedMedia.license}</a>
                        </small>
                    </div>
                )}
            </div>
        );
    },
}); 