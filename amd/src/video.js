import Plyr from 'mod_video/plyr';

export default class Video {
    constructor(instance) {
        const player = new Plyr(`#player-${instance.id}`);
    }
}
