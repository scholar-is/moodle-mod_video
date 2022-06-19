import Plyr from 'mod_video/plyr';
import Ajax from 'core/ajax';

export default class Video {

    constructor(cm, instance, options) {
        this.session = null;

        this.elapsedseconds = 0;

        this.player = new Plyr(`#player-${instance.id}`, options);

        if (options.resumeTime) {
            this.player.currentTime = parseInt(options.resumeTime);
        }

        setInterval(() => this.tick(), 1000);

        setInterval(() => this.recordUpdates(), 10000);

        this.player.on('playing', async () => {
            if (!this.sessionInitialized) {
                this.sessionInitialized = true;
                const response = await this.createSession(cm.id);
                this.session = response.session;
            }
        });
    }

    tick() {
        if (this.player && this.player.playing) {
            this.elapsedseconds += this.player.speed;
        }
    }

    recordUpdates() {
        if (this.player && this.elapsedseconds > 0) {
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
}
