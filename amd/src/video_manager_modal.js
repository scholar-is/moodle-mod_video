import Modal from 'core/modal';
import * as ModalRegistry from 'core/modal_registry';

export default class VideoManagerModal extends Modal {
    static TYPE = 'mod_video/video_manager_modal';
    static TEMPLATE = 'mod_video/video_manager_modal';
}

let registered = false;
if (!registered) {
    ModalRegistry.register(VideoManagerModal.TYPE, VideoManagerModal, VideoManagerModal.TEMPLATE);
    registered = true;
}
