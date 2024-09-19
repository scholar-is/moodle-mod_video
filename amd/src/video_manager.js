import Ajax from 'core/ajax';
import Notification from 'core/notification';
import Templates from 'core/templates';

export default class VideoManager {

    constructor(uniqueId, videoSourceType, options) {
        this.uniqueId = uniqueId;
        this.videoSourceType = videoSourceType;
        this.options = options;

        this.root = document.getElementById('video-manager-' + this.uniqueId);
        this.resultContainer = document.querySelector(`#video-manager-${this.uniqueId} .video-results-container`);
        this.searchInput = document.querySelector(`#video-manager-${this.uniqueId} input[name=search-videos-value]`);
        this.searchButton = document.querySelector(`#video-manager-${this.uniqueId} .search-videos-button`);
        this.loadingIconContainer = document.querySelector(`#video-manager-${this.uniqueId} .loading-icon-container`);

        this.videoResults = {
            total: 0,
            videos: [],
        };

        void this.init();
    }

    async init() {
        this.log("video_manager:init:uniqueid", this.uniqueId);
        this.log("video_manager:init:options", this.options);

        this.searchButton.addEventListener('click', async() => {
            await this.query();
            await this.refreshResults();
        });

        this.searchInput.addEventListener('keypress', async(e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                await this.query();
                await this.refreshResults();
            }
        });

        await this.query();
        await this.refreshResults();
    }

    async query() {
        this.log("video_manager:query", this.searchInput.value);
        this.setLoading(true);
        try {
            this.videoResults = (await this.queryVideos(this.searchInput.value)).results;
        } catch (e) {
            await Notification.exception(e);
        }
    }

    async refreshResults() {
        const renderedResults = await Promise.all(this.videoResults.videos.map(async(videoResult) => {
            const {html} = await Templates.renderForPromise('mod_video/video_manager_result', videoResult);
            return html;
        }));

        this.resultContainer.innerHTML = renderedResults.join('');

        const buttons = document.querySelectorAll(`#video-manager-${this.uniqueId} .select-video-button`);

        buttons.forEach((button) => {
            button.addEventListener('click', (e) => {
                const selectedVideoResult = this.videoResults.videos.find((r) => r.videoid === e.target.dataset.videoid);
                if (this.options.onVideoSelect) {
                    this.options.onVideoSelect(selectedVideoResult);
                }
            });
        });

        this.setLoading(false);
    }

    setLoading(loading) {
        if (loading) {
            this.loadingIconContainer.classList.remove('hidden');
        } else {
            this.loadingIconContainer.classList.add('hidden');
        }
    }

    queryVideos(query) {
        return Ajax.call([{
            methodname: 'mod_video_query_videos',
            args: {
                query,
                videosourcetype: this.videoSourceType,
            }
        }])[0];
    }

    /**
     * Wrapper for `console.log` that conditionally logs based on the `debug` setting for this cm.
     * @param {any} data
     */
    log(...data) {
        if (this.options.debug) {
            // eslint-disable-next-line no-console
            console.log(data);
        }
    }
}
