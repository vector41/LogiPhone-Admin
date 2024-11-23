export class MessageItem {
    constructor(
        id,
        sender_id,
        sender_name,
        sender_logi,
        receiver_id,
        receiver_name,
        receiver_logi
        send_time,
    ) {
        this.id = id;
        this.sender_id = sender_id;
        this.sender_name = sender_name;
        this.sender_logi = sender_logi;
        this.receiver_id = receiver_id,
        this.receiver_name = receiver_name,
        this.receiver_logi = receiver_logi;
        this.send_time = send_time;
    }
}

