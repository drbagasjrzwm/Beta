const musicUpload =
    document.getElementById("music-upload");


if (musicUpload) {

    musicUpload.addEventListener(
        "change",
        async function () {

            const file = this.files[0];


            if (!file) {
                return;
            }


            if (!file.name.toLowerCase().endsWith(".mp3")) {

                alert("Silakan pilih file MP3.");

                this.value = "";

                return;
            }


            /*
             * FormData untuk mengirim file ke PHP
             */

            const formData = new FormData();

            formData.append(
                "music",
                file
            );


            try {

                const response = await fetch("upload.php", {
                    method: "POST",
                    body: formData
                });


                const text = await response.text();

                console.log("Response PHP:", text);


                let result;

                try {

                    result = JSON.parse(text);

                } catch (error) {

                    console.error(
                        "Response PHP bukan JSON:",
                        text
                    );

                    alert(
                        "PHP mengembalikan error.\n\n" +
                        "Cek Console Browser (F12)."
                    );

                    return;
                }


                if (result.success) {

                    alert(
                        "🎵 Lagu berhasil ditambahkan!"
                    );

                    window.location.reload();

                } else {

                    alert(
                        "Gagal upload:\n" +
                        result.message
                    );
                }


            } catch (error) {

                console.error(
                    "Error upload:",
                    error
                );

                alert(
                    "Tidak dapat terhubung ke upload.php."
                );

            }


            /*
             * Reset input
             */

            this.value = "";

        }
    );

}


/* =========================================================
   MUSIC PLAYER
========================================================= */

const audioPlayer =
    document.getElementById("audio-player");

const playerTitle =
    document.getElementById("player-title");

const playerArtist =
    document.getElementById("player-artist");

const mainPlayBtn =
    document.getElementById("main-play-btn");

const prevBtn =
    document.getElementById("prev-btn");

const nextBtn =
    document.getElementById("next-btn");


const songCards = Array.from(
    document.querySelectorAll(".song-card")
);


let currentSongIndex = -1;


/*
 * Memainkan lagu
 */
function playSong(index) {

    if (index < 0 || index >= songCards.length) {
        return;
    }


    const song = songCards[index];


    const src = song.dataset.src;

    const title = song.dataset.title;

    const artist = song.dataset.artist;


    currentSongIndex = index;


    audioPlayer.src = src;

    playerTitle.textContent = title;

    playerArtist.textContent = artist;


    audioPlayer.play()
        .then(() => {

            mainPlayBtn.textContent = "⏸";

            updateSongButtons();

        })
        .catch((error) => {

            console.error(
                "Gagal memutar lagu:",
                error
            );

        });
}

/*
 * Update ikon tombol lagu
 */
function updateSongButtons() {
    songCards.forEach(
        (song, index) => {
            const button =
                song.querySelector(".play-btn");

            if (
                index === currentSongIndex &&
                !audioPlayer.paused
            ) {
                button.textContent = "⏸";
            } else {
                button.textContent = "▶";
            }
        }
    );
}

/*
 * Tombol play pada setiap lagu
 */
songCards.forEach(
    (song, index) => {
        const button =
            song.querySelector(".play-btn");
        button.addEventListener(
            "click",
            () => {
                if (
                    currentSongIndex === index &&
                    !audioPlayer.paused
                ) {
                    audioPlayer.pause();
                } else {
                    playSong(index);
                }
            }
        );
    }
);

/*
 * Tombol play utama
 */
mainPlayBtn.addEventListener(
    "click",
    () => {

        /*
         * Kalau belum ada lagu,
         * putar lagu pertama.
         */
        if (currentSongIndex === -1) {
            if (songCards.length > 0) {
                playSong(0);
            }
            return;
        }
        if (audioPlayer.paused) {
            audioPlayer.play();
            mainPlayBtn.textContent = "⏸";
        } else {
            audioPlayer.pause();
            mainPlayBtn.textContent = "▶";
        }
        updateSongButtons();
    }
);
/*
 * Lagu sebelumnya
 */
prevBtn.addEventListener(
    "click",
    () => {
        if (songCards.length === 0) {
            return;
        }
        let previousIndex =
            currentSongIndex - 1;
        if (previousIndex < 0) {
            previousIndex =
                songCards.length - 1;
        }
        playSong(previousIndex);
    }
);

/*
 * Lagu berikutnya
 */
nextBtn.addEventListener(
    "click",
    () => {
        if (songCards.length === 0) {
            return;
        }
        let nextIndex =
            currentSongIndex + 1;
        if (nextIndex >= songCards.length) {

            nextIndex = 0;
        }
        playSong(nextIndex);
    }
);

/*
 * Ketika lagu selesai,
 * otomatis memainkan lagu berikutnya.
 */
audioPlayer.addEventListener(
    "ended",
    () => {
        if (songCards.length === 0) {
            return;
        }

        let nextIndex =
            currentSongIndex + 1;
        if (nextIndex >= songCards.length) {
            nextIndex = 0;
        }
        playSong(nextIndex);
    }
);

/*
 * Ketika player di-pause.
 */
audioPlayer.addEventListener(
    "pause",
    () => {
        mainPlayBtn.textContent = "▶";
        updateSongButtons();
    }
);

/*
 * Ketika player sedang dimainkan.
 */
audioPlayer.addEventListener(
    "play",
    () => {
        mainPlayBtn.textContent = "⏸";
        updateSongButtons();
    }
);

/* =========================================================
   DELETE / HAPUS LAGU
========================================================= */

const deleteButtons =
    document.querySelectorAll(".delete-btn");

deleteButtons.forEach(
    (button) => {
        button.addEventListener(
            "click",
            async () => {

                const id =
                    button.dataset.id;
                const title =
                    button.dataset.title;

                /*
                 * Konfirmasi sebelum menghapus
                 */
                const confirmDelete =
                    confirm(
                        `Apakah kamu yakin ingin menghapus lagu "${title}"?`
                    );

                if (!confirmDelete) {
                    return;
                }

                /*
                 * Siapkan data untuk dikirim ke PHP
                 */
                const formData =
                    new FormData();

                formData.append(
                    "id",
                    id
                );

                try {

                    const response =
                        await fetch(
                            "delete.php",
                            {
                                method: "POST",
                                body: formData
                            }
                        );

                    const text =
                        await response.text();

                    console.log(
                        "Response delete.php:",
                        text
                    );

                    let result;

                    try {
                        result =
                            JSON.parse(text);

                    } catch (error) {
                        console.error(
                            "Response delete.php bukan JSON:",
                            text
                        );
                        alert(
                            "Terjadi error pada PHP saat menghapus lagu.\n\n" +
                            "Cek Console Browser (F12)."
                        );
                        return;
                    }

                    /*
                     * Jika berhasil
                     */
                    if (result.success) {
                        alert(
                            "🗑️ Lagu berhasil dihapus!"
                        );
                        /*
                         * Reload halaman agar
                         * daftar lagu diperbarui
                      */
                        window.location.reload();

                    } else {
                        alert(
                            "Gagal menghapus lagu:\n\n" +
                            result.message
                        );
                    }

                } catch (error) {
                    console.error(
                        "Error delete:",
                        error
                    );

                    alert(
                        "Tidak dapat terhubung ke delete.php."
                    );
                }
            }
        );
    }
);