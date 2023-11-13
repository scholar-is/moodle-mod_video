import Ajax from 'core/ajax';
import {exception as displayException} from 'core/notification';
import Templates from 'core/templates';

export default class VideoManager {

    constructor(uniqueId, options) {
        this.uniqueId = uniqueId;
        this.options = options;

        this.root = document.getElementById('video-manager-' + this.uniqueId);
        this.resultContainer = document.querySelector(`#video-manager-${this.uniqueId} .card-columns`);
        this.searchInput = document.querySelector(`#video-manager-${this.uniqueId} input[name=search-videos-value]`);
        this.searchButton = document.querySelector(`#video-manager-${this.uniqueId} .search-videos-button`);

        this.videoResults = [];

        void this.init();
    }

    async init() {
        this.log("video_manager:init:uniqueid", this.uniqueId);
        this.log("video_manager:init:options", this.options);

        this.searchButton.addEventListener('click', async () => {
            console.log("CLICK");

            await this.query();
            await this.refreshResults();
        });

        this.searchInput.addEventListener('keypress', async (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                await this.query();
                await this.refreshResults();
            }
        });
    }

    async query() {
        this.videoResults = (await this.queryVideos(this.searchInput.value)).results;
    }

    async refreshResults() {
        const renderedResults = await Promise.all(this.videoResults.map(async (videoResult) => {
            const { html } = await Templates.renderForPromise('mod_video/video_manager_result', videoResult);
            return html;
        }));

        this.resultContainer.innerHTML = renderedResults.join('');

        const queryString = `#video-manager-${this.uniqueId} .select-video-button`;
        const buttons = document.querySelectorAll(`#video-manager-${this.uniqueId} .select-video-button`);

        console.log(queryString, buttons);

        buttons.forEach((button) => {
            console.log(button);
            button.addEventListener('click', (e) => {
                const selectedVideoResult = this.videoResults.find((r) => r.videoid === e.target.dataset.videoid);
                if (this.options.onVideoSelect) {
                    this.options.onVideoSelect(selectedVideoResult);
                }
            });
        });
    }

    queryVideos(query) {
        return Ajax.call([{
            methodname: 'mod_video_query_videos',
            args: {
                query
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
