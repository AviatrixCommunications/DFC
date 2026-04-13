
export default function initVideoPlayer() {
  document.addEventListener('DOMContentLoaded', () => {
    const videos = document.querySelectorAll('.single-featured-video video');

    videos.forEach(video => {
      const playButton = video.parentElement.querySelector('.video-play-button');
      if (!playButton) return;

      const container = playButton.closest('.video-container');

      // Set fixed height on load to prevent jump
      function setVideoHeight() {
        if (video.videoHeight && video.videoWidth) {
          const aspectRatio = video.videoHeight / video.videoWidth;
          const width = container.offsetWidth;
          container.style.height = (width * aspectRatio) + 'px';
        }
      }

      // Load video metadata to get dimensions
      video.addEventListener('loadedmetadata', setVideoHeight);

      // Play button click handler
      playButton.addEventListener('click', function() {
        video.controls = true;
        video.play();
        playButton.style.display = 'none';
        setVideoHeight();
        // Move focus to video controls
        video.focus();
      });

      // Show button when paused
      video.addEventListener('pause', function() {
        if (!video.ended) {
          video.controls = false;
          playButton.style.display = 'block';
          // Return focus to play button
          playButton.focus();
        }
      });

      // Hide button when playing
      video.addEventListener('play', function() {
        playButton.style.display = 'none';
      });

      // Handle window resize
      window.addEventListener('resize', setVideoHeight);
    });
  });
}
