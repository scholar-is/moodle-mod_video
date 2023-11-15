import VideoManager from 'mod_video/video_manager';
import VideoManagerModal from 'mod_video/video_manager_modal';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import $ from 'jquery';

export function init(uniqueId) {
    let videoManagerInstance = null;

    ModalFactory.create({
        type: VideoManagerModal.TYPE,
        templateContext: { uniqueid: uniqueId },
        large: true,
    }, $('#id_searchvideos')).then(function (modal) {
        modal.getRoot().on(ModalEvents.shown, function () {
            if (!videoManagerInstance) {
                videoManagerInstance = new VideoManager(uniqueId, {
                    onVideoSelect: (selectedVideo) => {
                        document.getElementById('id_videoid').value = selectedVideo.videoid;
                        modal.hide();
                    },
                    debug: true}
                );
            }
        });

        modal.getRoot().on('click', '.close-button', () => {
            modal.hide();
        });
    });
}
