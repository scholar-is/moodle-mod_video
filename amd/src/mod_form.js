import VideoManager from 'mod_video/video_manager';
import VideoManagerModal from 'mod_video/video_manager_modal';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import $ from 'jquery';

/**
 * Initialize functionality for module create/edit form.
 * @param uniqueId
 * @param videoSourceType
 * @param inputId
 * @param debug
 */
export function init(uniqueId, videoSourceType, inputId, debug) {
    let videoManagerInstance = null;

    ModalFactory.create({
        type: VideoManagerModal.TYPE,
        templateContext: { uniqueid: uniqueId },
        large: true,
    }, $(`#id_searchvideos_${videoSourceType}`)).then(function (modal) {
        modal.getRoot().on(ModalEvents.shown, function () {
            if (!videoManagerInstance) {
                videoManagerInstance = new VideoManager(
                    uniqueId,
                    videoSourceType,
                    {
                        onVideoSelect: (selectedVideo) => {
                            document.getElementById(inputId).value = selectedVideo.videoid;
                            modal.hide();
                        },
                        debug,
                    }
                );
            }
        });

        modal.getRoot().on('click', '.close-button', () => {
            modal.hide();
        });
    });
}
