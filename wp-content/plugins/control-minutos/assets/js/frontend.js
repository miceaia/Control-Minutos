(function ($) {
    const settings = window.controlMinutosFrontend || {};
    const selector = settings.selector || 'video';
    const debounce = (fn, delay) => {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => fn.apply(null, args), delay);
        };
    };

    function formatMinutes(seconds) {
        if (!seconds) {
            return '0';
        }
        const minutes = seconds / 60;
        const rounded = Math.round(minutes * 10) / 10;
        return Number.isInteger(rounded) ? rounded.toString() : rounded.toFixed(1);
    }

    function ensureCounter($video, totalSeconds) {
        let $counter = $video.closest('.avppro-player, .video-wrapper, .wp-video').find('.control-minutos-counter');
        if (!$counter.length) {
            $counter = $('<div>', { class: 'control-minutos-counter' });
            $video.after($counter);
        }

        $counter.data('total', totalSeconds);
        return $counter;
    }

    function updateCounter($counter, watchedSeconds) {
        const total = parseInt($counter.data('total'), 10) || 0;
        const unit = settings.strings?.unit || 'min';
        const consumedText = `${settings.strings?.consumed || 'Consumido'} ${formatMinutes(watchedSeconds)} / ${formatMinutes(total)} ${unit}`;
        const remainingSeconds = Math.max(total - watchedSeconds, 0);
        const remainingText = `${settings.strings?.remaining || 'Restan'} ${formatMinutes(remainingSeconds)} ${unit}`;
        $counter.html(`<span class="cm-counter-consumed">${consumedText}</span><span class="cm-counter-remaining">${remainingText}</span>`);
    }

    function sendProgress(videoElement, watchedSeconds, totalSeconds) {
        if (!settings.endpoint) {
            return;
        }

        const safeWatched = Number.isFinite(watchedSeconds) ? watchedSeconds : 0;
        const safeTotal = Number.isFinite(totalSeconds) ? totalSeconds : 0;

        if (safeTotal <= 0) {
            return;
        }

        const context = settings.context || {};
        const payload = {
            video_id: videoElement.dataset.videoId || videoElement.id || videoElement.currentSrc,
            seconds_watched: safeWatched,
            total_seconds: safeTotal,
            course_id: videoElement.dataset.courseId || context.courseId || '',
            lesson_id: videoElement.dataset.lessonId || context.lessonId || ''
        };

        if (!payload.video_id) {
            return;
        }

        fetch(settings.endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': settings.nonce || ''
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload)
        });
    }

    function bindVideo(video) {
        if (video.dataset.controlMinutosBound) {
            return;
        }

        video.dataset.controlMinutosBound = '1';
        const $video = $(video);
        const totalSeconds = Math.round(video.duration || parseFloat(video.dataset.duration) || 0);
        const $counter = ensureCounter($video, totalSeconds);
        const debouncedSend = debounce((element, watchedSeconds, totalSecondsValue) => {
            sendProgress(element, watchedSeconds, totalSecondsValue);
        }, 2000);

        updateCounter($counter, Math.round(video.currentTime || 0));

        video.addEventListener('loadedmetadata', () => {
            const total = Math.round(video.duration);
            $counter.data('total', total);
            updateCounter($counter, Math.round(video.currentTime));
        });

        video.addEventListener('timeupdate', () => {
            const watchedSeconds = Math.round(video.currentTime);
            const total = Math.round(video.duration || $counter.data('total') || 0);
            updateCounter($counter, watchedSeconds);
            debouncedSend(video, watchedSeconds, total);
        });

        video.addEventListener('ended', () => {
            const total = Math.round(video.duration || $counter.data('total') || 0);
            updateCounter($counter, total);
            sendProgress(video, total, total);
        });

        if (settings.endpoint) {
            fetch(`${settings.endpoint}?video_id=${encodeURIComponent(video.dataset.videoId || video.id || video.currentSrc)}`, {
                credentials: 'same-origin',
                headers: {
                    'X-WP-Nonce': settings.nonce || ''
                }
            })
                .then((response) => response.json())
                .then((data) => {
                    if (!data) {
                        return;
                    }
                    const watchedSeconds = parseInt(data.seconds_watched, 10) || 0;
                    const total = parseInt(data.total_seconds, 10) || totalSeconds;
                    const remaining = typeof data.remaining_seconds !== 'undefined' ? parseInt(data.remaining_seconds, 10) : Math.max(total - watchedSeconds, 0);
                    $counter.data('total', total);
                    updateCounter($counter, watchedSeconds);
                    $counter.data('remaining', remaining);
                    if (watchedSeconds) {
                        video.currentTime = watchedSeconds;
                    }
                })
                .catch(() => {
                    // Silently ignore errors.
                });
        }
    }

    function init() {
        const videos = document.querySelectorAll(selector);
        videos.forEach(bindVideo);

        if (window.MutationObserver) {
            const observer = new MutationObserver(() => {
                document.querySelectorAll(selector).forEach((video) => {
                    bindVideo(video);
                });
            });

            observer.observe(document.body, { childList: true, subtree: true });
        }
    }

    $(document).ready(init);
})(jQuery);
