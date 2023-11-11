import Ajax from 'core/ajax';

export default class VideoManager {

    constructor(cm, instance) {
        this.cm = cm;
        this.instance = instance;
        this.options = {
            forceDuration: true,
            ...options,
        };
        this.session = null;

        this.root = document.getElementById(`video-${this.instance.id}`);

        void this.init();
    }

    async init() {
        this.log("video:init:cm", this.cm);
        this.log("video:init:instance", this.instance);
        this.log("video:init:options", this.options);
    }

    queryVideos(cmid, query) {
        return Ajax.call([{
            methodname: 'mod_video_query_videos',
            args: {
                cmid,
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

/**
 * Get the time user is seeking to.
 * @param {Player} plyr
 * @param {HTMLInputElement} input
 * @returns {number}
 * @private
 */
function _getTargetTime(plyr, input) {
    if (typeof input === "object" && (input.type === "input" || input.type === "change")) {
        return input.target.value / input.target.max * plyr.media.duration;
    } else {
        return Number(input);
    }
}