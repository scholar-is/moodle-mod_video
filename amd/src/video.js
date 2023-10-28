import Plyr from 'mod_video/plyr';
import Ajax from 'core/ajax';
import * as Toast from 'core/toast';
import {get_strings as getStrings} from 'core/str';

const UPDATE_INTERVAL_SECONDS = 10;

export default class Video {

    constructor(cm, instance, options) {
        this.cm = cm;
        this.instance = instance;
        this.options = {
            forceDuration: true,
            ...options,
        };
        this.session = null;

        this.elapsedseconds = 0;
        this.maxTime = options.sessionAggregates.maxtime ?? 0;
        this.root = document.getElementById(`video-${this.instance.id}`);

        void this.init();
    }

    async init() {
        this.log("video:init:cm", this.cm);
        this.log("video:init:instance", this.instance);
        this.log("video:init:options", this.options);

        const stringKeys = [
            {
                key: 'cannotforwardseek',
                component: 'mod_video'
            },
        ];
        this.strings = await getStrings(stringKeys);

        if (this.options.preventForwardSeeking) {
            this.options.listeners = {
                seek: (e) => {
                    this.log("video:seek", this.player.currentTime, e);
                    this.log("strings", this.strings);
                    if (_getTargetTime(this.player, e) > this.maxTime) {
                        this.log("video:preventff", this.player.currentTime, e);
                        if (!document.querySelector('.toast-wrapper .toast')) {
                            Toast.add(this.strings[0]);
                        }
                        return false;
                    }
                },
            };
        }

        this.player = new Plyr(`#player-${this.instance.id}`, this.options);

        window.player = this.player;

        this.player.on('playing', async (event) => {
            this.log("player:playing", event);
            if (!this.sessionInitialized) {
                this.sessionInitialized = true;
                const response = await this.createSession(this.cm.id);
                this.session = response.session;
            }
        });

        this.player.on('ready', (event) => {
            this.log("player:ready", event);

            if (this.instance.resume === "1" && this.options.sessionAggregates.lasttime) {
                const resumeTime = parseInt(this.options.sessionAggregates.lasttime);
                this.log("video:resume", resumeTime);
                // Note: There's a bug when setting the current time for videos:
                // the progress bar will not update until the video is played.
                if (event.detail.plyr.embed.setCurrentTime) {
                    event.detail.plyr.embed.setCurrentTime(resumeTime);
                } else {
                    // Vimeo.
                    event.detail.plyr.currentTime = resumeTime;
                }
            }

            this.root.classList.add('ready');
        });

        this.player.on('pause', () => {
            this.recordUpdates();
        });
        this.player.on('ended', () => {
            this.recordUpdates();
        });

        this.player.on('error', (event) => {
            console.error(event);
        });

        this.log("video:player", this.player);

        setInterval(() => this.tick(), 1000);

        setInterval(() => this.recordUpdates(), UPDATE_INTERVAL_SECONDS * 1000);
    }

    tick() {
        if (this.player && this.player.playing) {
            this.elapsedseconds += this.player.speed;

            if (this.player.currentTime > this.maxTime) {
                this.maxTime = this.player.currentTime;
            }
        }
    }

    recordUpdates() {
        if (this.player && this.elapsedseconds > 0 && this.sessionInitialized) {
            Ajax.call([{
                methodname: 'mod_video_record_session_updates',
                args: {
                    sessionid: this.session.id,
                    timeelapsed: this.elapsedseconds,
                    currenttime: parseInt(this.player.currentTime),
                    currentpercent: this.player.currentTime / this.player.duration,
                }
            }]);
            this.elapsedseconds = 0;
        }
    }

    createSession(cmid) {
        return Ajax.call([{
            methodname: 'mod_video_create_session',
            args: { cmid }
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