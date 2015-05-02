#!/bin/sh
sleep 1
now=$(date +"%Y-%m-%d_%H-%M-%S")
ffmpeg -y -i rtmp://localhost/esibun/live_esibun -vcodec copy -acodec copy /srv/rec/videos/esihd-$now.mp4
