
    // Keep menus open while the pointer travels from the heading to an action.
    const editorMenuGroups = [...document.querySelectorAll('.nle-menu-group')];
    function closeEditorMenus() {
        editorMenuGroups.forEach(group => {
            group.classList.remove('is-open');
            group.querySelector('.nle-menu-item').setAttribute('aria-expanded', 'false');
        });
    }
    editorMenuGroups.forEach((group, index) => {
        const trigger = group.querySelector('.nle-menu-item');
        const dropdown = group.querySelector('.nle-menu-dropdown');
        dropdown.id = `editor-menu-${index}`;
        trigger.setAttribute('aria-controls', dropdown.id);
        trigger.setAttribute('aria-expanded', 'false');
        const openMenu = () => {
            closeEditorMenus();
            group.classList.add('is-open');
            trigger.setAttribute('aria-expanded', 'true');
        };
        trigger.addEventListener('pointerenter', event => {
            if (event.pointerType !== 'touch') openMenu();
        });
        trigger.addEventListener('click', openMenu);
        dropdown.addEventListener('click', event => {
            if (event.target.closest('.nle-menu-link:not([disabled])')) closeEditorMenus();
        });
    });
    document.addEventListener('click', event => {
        if (!event.target.closest('.nle-menu-group')) closeEditorMenus();
    });
    document.addEventListener('keydown', event => {
        if (event.key !== 'Escape') return;
        const openGroup = document.querySelector('.nle-menu-group.is-open');
        if (openGroup) {
            closeEditorMenus();
            openGroup.querySelector('.nle-menu-item').focus();
        }
    });

    // State
    let isPlaying = false;
    let isLooping = false;
    let currentSpeed = 1.0;
    let mixerMuted = false;
    let mixerSolo = false;
    let mixerDim = false;
    let masterVolume = 0.85;
    let meterTimer = null;
    let audioCtx = null;
    let nleAmbienceGain = null;
    let isNleAmbiencePlaying = false;
    let nleAmbienceOscillators = [];

    // 1. Workspace Switcher
    function switchWorkspace(name) {
        document.querySelectorAll('.workspace-tab').forEach(t => {
            t.classList.toggle('active', t.dataset.workspace === name);
        });
        document.querySelectorAll('.nle-workspace-view').forEach(v => {
            v.classList.toggle('active', v.id === 'workspace-' + name);
        });
    }

    // 2. Inspector Tab Switcher (5 tabs: video, vfx, titles, transitions, ai)
    function switchInspTab(tabName) {
        document.querySelectorAll('.insp-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.insp-content').forEach(c => c.classList.remove('active'));
        
        const tabMap = { video: 0, vfx: 1, titles: 2, transitions: 3, ai: 4 };
        const idx = tabMap[tabName] ?? 0;
        const tabs = document.querySelectorAll('.insp-tab');
        if (tabs[idx]) tabs[idx].classList.add('active');

        const content = document.getElementById('insp-' + tabName);
        if (content) content.classList.add('active');
    }

    // 3. Playback Controls
    function togglePlay() {
        const video = document.getElementById('mainPlayer');
        if (!video) return;
        if (video.paused) {
            video.play().then(() => setPlayIcon(true)).catch(() => {});
        } else {
            video.pause();
            setPlayIcon(false);
        }
    }

    function setPlayIcon(playing) {
        isPlaying = playing;
        const btn = document.getElementById('playPauseBtn');
        if (!btn) return;
        btn.classList.toggle('bi-play-fill', !playing);
        btn.classList.toggle('bi-pause-fill', playing);
        btn.style.color = playing ? '#0dcaf0' : '#fff';
    }

    function seekToStart() {
        const video = document.getElementById('mainPlayer');
        if (video) { video.currentTime = 0; updateTimecode(); }
    }

    function seekToEnd() {
        const video = document.getElementById('mainPlayer');
        if (video && !isNaN(video.duration)) { video.currentTime = Math.max(0, video.duration - 0.05); updateTimecode(); }
    }

    function stepFrame(dir) {
        const video = document.getElementById('mainPlayer');
        if (!video) return;
        video.pause();
        setPlayIcon(false);
        video.currentTime = Math.max(0, video.currentTime + (dir / 24));
        updateTimecode();
    }

    function toggleLoop() {
        isLooping = !isLooping;
        const video = document.getElementById('mainPlayer');
        if (video) video.loop = isLooping;
        document.getElementById('btnLoopIcon')?.classList.toggle('text-info', isLooping);
    }

    function setSpeed(speed) {
        currentSpeed = speed;
        const video = document.getElementById('mainPlayer');
        if (video) video.playbackRate = speed;
        document.querySelectorAll('.speed-btn').forEach(btn => {
            btn.classList.toggle('text-white', btn.textContent.includes(speed + 'x'));
            btn.classList.toggle('active', btn.textContent.includes(speed + 'x'));
        });
    }

    function toggleFullscreen() {
        const viewport = document.getElementById('monitorViewport');
        if (!document.fullscreenElement) {
            viewport?.requestFullscreen().catch(() => {});
        } else {
            document.exitFullscreen().catch(() => {});
        }
    }

    // 4. Scope Letterbox Masking Engine
    function setScopeMask(type) {
        const top = document.getElementById('maskTop');
        const bottom = document.getElementById('maskBottom');
        const left = document.getElementById('maskLeft');
        const right = document.getElementById('maskRight');
        const selector = document.getElementById('scopeMaskSelector');

        if (selector) selector.value = type;

        // Reset
        top.style.height = '0%';
        bottom.style.height = '0%';
        left.style.width = '0%';
        right.style.width = '0%';

        if (type === 'anamorphic') {
            // 2.39:1 Scope
            top.style.height = '12.5%';
            bottom.style.height = '12.5%';
        } else if (type === 'vertical') {
            // 9:16 Vertical Phone
            left.style.width = '35%';
            right.style.width = '35%';
        } else if (type === 'classic43') {
            // 4:3 Academy
            left.style.width = '12.5%';
            right.style.width = '12.5%';
        }
    }

    // 5. Live Title Generator
    function updateLiveTitle() {
        const headline = document.getElementById('titleText')?.value || '';
        const subtitle = document.getElementById('subtitleText')?.value || '';
        const font = document.getElementById('titleFont')?.value || "'Syncopate', sans-serif";
        const color = document.getElementById('titleColor')?.value || '#ffffff';
        const glow = document.getElementById('titleGlowRange')?.value || 10;
        const visible = document.getElementById('titleVisible')?.checked ?? true;
        const pos = document.getElementById('titlePosition')?.value || 'center';

        const glowVal = document.getElementById('titleGlowVal');
        if (glowVal) glowVal.textContent = glow + 'px';

        const overlay = document.getElementById('canvasTitleOverlay');
        const elHead = document.getElementById('overlayHeadline');
        const elSub = document.getElementById('overlaySubtitle');

        if (!overlay || !elHead || !elSub) return;

        overlay.style.display = visible ? 'block' : 'none';
        elHead.textContent = headline;
        elSub.textContent = subtitle;

        elHead.style.fontFamily = font;
        elHead.style.color = color;
        elHead.style.textShadow = `0 2px ${glow}px rgba(0,0,0,0.95), 0 0 ${glow}px ${color}`;

        // Positioning
        if (pos === 'center') {
            overlay.style.bottom = '40%';
            elHead.style.fontSize = '2rem';
        } else if (pos === 'lower_third') {
            overlay.style.bottom = '12%';
            overlay.style.textAlign = 'left';
            elHead.style.fontSize = '1.4rem';
        } else if (pos === 'subtitle') {
            overlay.style.bottom = '6%';
            overlay.style.textAlign = 'center';
            elHead.style.fontSize = '1.1rem';
        } else if (pos === 'watermark') {
            overlay.style.bottom = '85%';
            overlay.style.textAlign = 'right';
            elHead.style.fontSize = '0.9rem';
        }
    }

    // 6. Live Transitions & FX Engine
    let currentSelectedTransition = 'fade';

    function selectTransition(type, el) {
        currentSelectedTransition = type;
        document.querySelectorAll('.trans-card').forEach(c => c.classList.remove('active'));
        if (el) el.classList.add('active');
        triggerTransition(type);
    }

    function setTransCategory(cat, btn) {
        document.querySelectorAll('.trans-cat-pill').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');

        const cards = document.querySelectorAll('.trans-card');
        let count = 0;
        cards.forEach(c => {
            if (cat === 'all' || c.getAttribute('data-category') === cat) {
                c.style.display = 'block';
                count++;
            } else {
                c.style.display = 'none';
            }
        });
        const badge = document.getElementById('transCountBadge');
        if (badge) badge.textContent = `${count} Presets`;
    }

    function filterTransitions() {
        const query = (document.getElementById('transSearchInput')?.value || '').toLowerCase().trim();
        const cards = document.querySelectorAll('.trans-card');
        let count = 0;
        cards.forEach(c => {
            const text = c.textContent.toLowerCase();
            if (!query || text.includes(query)) {
                c.style.display = 'block';
                count++;
            } else {
                c.style.display = 'none';
            }
        });
        const badge = document.getElementById('transCountBadge');
        if (badge) badge.textContent = `${count} Presets`;
    }

    function updateTransDurationVal(val) {
        const span = document.getElementById('transDurationVal');
        if (span) span.textContent = (val / 1000).toFixed(1) + 's';
    }

    function setTransDurationPreset(ms) {
        const input = document.getElementById('transDuration');
        if (input) {
            input.value = ms;
            updateTransDurationVal(ms);
        }
    }

    function playSelectedTransition() {
        triggerTransition(currentSelectedTransition);
    }

    function applyTransitionToClip(target) {
        const toast = document.getElementById('transToast');
        const msg = document.getElementById('transToastMsg');
        if (!toast || !msg) return;

        const niceName = currentSelectedTransition.replace('_', ' ').toUpperCase();
        if (target === 'all') {
            const cuts = document.querySelectorAll('.clip-video').length || 1;
            msg.innerHTML = `<strong>${niceName}</strong> applied to all ${cuts} cuts in timeline!`;
        } else {
            msg.innerHTML = `<strong>${niceName}</strong> applied to current sequence cut!`;
        }
        toast.classList.remove('d-none');
        playSelectedTransition();

        setTimeout(() => {
            toast.classList.add('d-none');
        }, 3500);
    }

    function triggerTransition(type) {
        const video = document.getElementById('mainPlayer');
        const overlay = document.getElementById('transFxOverlay');
        if (!video) return;

        const durMs = parseInt(document.getElementById('transDuration')?.value || 800);
        const durSec = durMs / 1000;
        const dir = document.getElementById('transDirection')?.value || 'lr';
        const easing = document.getElementById('transEasing')?.value || 'ease-in-out';
        const intensity = (parseInt(document.getElementById('transIntensity')?.value || 80)) / 100;
        const syncAudio = document.getElementById('transAudioSync')?.checked;
        const audioProfile = document.getElementById('transAudioProfile')?.value || 'auto';

        // Auto-play Synced Sound FX
        if (syncAudio) {
            let soundToPlay = audioProfile;
            if (audioProfile === 'auto') {
                if (type === 'flash' || type === 'laser_streak') soundToPlay = 'laser_whoosh';
                else if (type === 'glitch' || type === 'vhs_roll' || type === 'pixelate') soundToPlay = 'vhs_glitch';
                else if (type === 'camera_shake' || type === 'cube_flip') soundToPlay = 'bass_boom';
                else if (type === 'bloom' || type === 'color_dip') soundToPlay = 'tension_riser';
                else if (type === 'film_burn') soundToPlay = 'laser_whoosh';
                else soundToPlay = 'laser_whoosh';
            }
            if (soundToPlay !== 'none') {
                playSfx(soundToPlay);
            }
        }

        video.style.transition = `all ${durSec / 2}s ${easing}`;
        if (overlay) {
            overlay.style.transition = `all ${durSec / 2}s ${easing}`;
            overlay.style.background = 'none';
            overlay.style.opacity = '0';
            overlay.style.filter = 'none';
            overlay.style.clipPath = 'none';
        }

        const halfDur = (durMs / 2);

        // Transition Animation Logic
        if (type === 'fade') {
            video.style.opacity = '0';
            setTimeout(() => { video.style.opacity = '1'; }, halfDur);
        } 
        else if (type === 'flash') {
            video.style.filter = `brightness(${200 + intensity * 200}%) contrast(${120 + intensity * 60}%)`;
            if (overlay) {
                overlay.style.background = 'rgba(255, 255, 255, 0.95)';
                overlay.style.opacity = `${intensity}`;
            }
            setTimeout(() => { 
                video.style.filter = 'none'; 
                if (overlay) overlay.style.opacity = '0';
            }, halfDur);
        }
        else if (type === 'film_burn') {
            video.style.filter = `sepia(${intensity * 0.8}) brightness(180%) contrast(140%)`;
            if (overlay) {
                overlay.style.background = 'radial-gradient(circle at 60% 40%, rgba(255, 140, 0, 0.9) 0%, rgba(255, 40, 0, 0.7) 40%, rgba(255, 240, 100, 0.95) 70%, transparent 100%)';
                overlay.style.opacity = `${intensity * 0.95}`;
            }
            setTimeout(() => {
                video.style.filter = 'none';
                if (overlay) overlay.style.opacity = '0';
            }, halfDur);
        }
        else if (type === 'bloom') {
            video.style.filter = `blur(${intensity * 12}px) brightness(200%) saturate(200%)`;
            if (overlay) {
                overlay.style.background = 'radial-gradient(circle, rgba(13, 202, 240, 0.5) 0%, rgba(255, 255, 255, 0.7) 50%, transparent 80%)';
                overlay.style.opacity = `${intensity * 0.8}`;
            }
            setTimeout(() => {
                video.style.filter = 'none';
                if (overlay) overlay.style.opacity = '0';
            }, halfDur);
        }
        else if (type === 'color_dip') {
            video.style.filter = `hue-rotate(180deg) saturate(350%) brightness(150%)`;
            if (overlay) {
                overlay.style.background = 'rgba(13, 202, 240, 0.6)';
                overlay.style.opacity = `${intensity * 0.8}`;
            }
            setTimeout(() => {
                video.style.filter = 'none';
                if (overlay) overlay.style.opacity = '0';
            }, halfDur);
        }
        else if (type === 'wipe') {
            let trans = 'translateX(-100%)';
            if (dir === 'rl') trans = 'translateX(100%)';
            else if (dir === 'tb') trans = 'translateY(-100%)';
            else if (dir === 'bt') trans = 'translateY(100%)';
            else if (dir === 'center') trans = 'scale(0.05)';

            video.style.transform = trans;
            video.style.opacity = '0.4';
            setTimeout(() => {
                video.style.transform = 'none';
                video.style.opacity = '1';
            }, halfDur);
        }
        else if (type === 'barn_door') {
            if (overlay) {
                overlay.style.background = 'linear-gradient(90deg, #000 50%, transparent 50%), linear-gradient(-90deg, #000 50%, transparent 50%)';
                overlay.style.opacity = '1';
            }
            video.style.transform = 'scale(0.92)';
            setTimeout(() => {
                video.style.transform = 'none';
                if (overlay) overlay.style.opacity = '0';
            }, halfDur);
        }
        else if (type === 'radial_iris') {
            video.style.clipPath = 'circle(0% at 50% 50%)';
            if (overlay) {
                overlay.style.background = '#000';
                overlay.style.opacity = '0.8';
            }
            setTimeout(() => {
                video.style.clipPath = 'circle(100% at 50% 50%)';
                if (overlay) overlay.style.opacity = '0';
            }, halfDur);
        }
        else if (type === 'diagonal_slice') {
            video.style.transform = 'skew(-20deg) translateX(-80%)';
            video.style.opacity = '0.3';
            setTimeout(() => {
                video.style.transform = 'none';
                video.style.opacity = '1';
            }, halfDur);
        }
        else if (type === 'venetian_blinds') {
            if (overlay) {
                overlay.style.background = 'repeating-linear-gradient(0deg, #000, #000 12px, transparent 12px, transparent 24px)';
                overlay.style.opacity = `${intensity * 0.9}`;
            }
            video.style.filter = 'brightness(140%)';
            setTimeout(() => {
                video.style.filter = 'none';
                if (overlay) overlay.style.opacity = '0';
            }, halfDur);
        }
        else if (type === 'zoom') {
            video.style.transform = `scale(${1.2 + intensity * 0.8})`;
            video.style.filter = `blur(${intensity * 10}px)`;
            setTimeout(() => { 
                video.style.transform = 'none';
                video.style.filter = 'none';
            }, halfDur);
        }
        else if (type === 'zoom_out') {
            video.style.transform = `scale(${0.7 - intensity * 0.3})`;
            video.style.filter = `blur(${intensity * 8}px)`;
            setTimeout(() => {
                video.style.transform = 'none';
                video.style.filter = 'none';
            }, halfDur);
        }
        else if (type === 'vortex_spin') {
            video.style.transform = `rotate(360deg) scale(${0.8 - intensity * 0.2})`;
            video.style.filter = `blur(${intensity * 8}px) hue-rotate(90deg)`;
            setTimeout(() => {
                video.style.transform = 'none';
                video.style.filter = 'none';
            }, halfDur);
        }
        else if (type === 'motion_blur_push') {
            const shift = dir === 'rl' ? '70%' : '-70%';
            video.style.transform = `translateX(${shift}) scale(1.05)`;
            video.style.filter = `blur(${intensity * 14}px)`;
            setTimeout(() => {
                video.style.transform = 'none';
                video.style.filter = 'none';
            }, halfDur);
        }
        else if (type === 'camera_shake') {
            let count = 0;
            const shakeInterval = setInterval(() => {
                const rx = (Math.random() - 0.5) * 30 * intensity;
                const ry = (Math.random() - 0.5) * 20 * intensity;
                const rrot = (Math.random() - 0.5) * 6 * intensity;
                video.style.transform = `translate(${rx}px, ${ry}px) rotate(${rrot}deg)`;
                count++;
                if (count > 6) {
                    clearInterval(shakeInterval);
                    video.style.transform = 'none';
                }
            }, 40);
        }
        else if (type === 'cube_flip') {
            video.style.transform = 'perspective(900px) rotateY(90deg) scale(0.85)';
            video.style.opacity = '0.3';
            setTimeout(() => {
                video.style.transform = 'none';
                video.style.opacity = '1';
            }, halfDur);
        }
        else if (type === 'glitch') {
            video.style.filter = 'hue-rotate(180deg) saturate(350%) contrast(150%)';
            video.style.transform = `skewX(${intensity * 14}deg) scale(1.06)`;
            if (overlay) {
                overlay.style.background = 'repeating-linear-gradient(0deg, rgba(255,0,0,0.3) 0, rgba(0,255,255,0.3) 3px, transparent 4px, transparent 8px)';
                overlay.style.opacity = '1';
            }
            setTimeout(() => { 
                video.style.filter = 'none';
                video.style.transform = 'none';
                if (overlay) overlay.style.opacity = '0';
            }, halfDur);
        }
        else if (type === 'vhs_roll') {
            video.style.filter = 'grayscale(60%) contrast(160%)';
            if (overlay) {
                overlay.style.background = 'linear-gradient(180deg, rgba(255,255,255,0.8) 0%, transparent 15%, rgba(0,0,0,0.8) 85%, rgba(255,255,255,0.8) 100%)';
                overlay.style.opacity = `${intensity * 0.9}`;
            }
            video.style.transform = 'translateY(8px)';
            setTimeout(() => {
                video.style.filter = 'none';
                video.style.transform = 'none';
                if (overlay) overlay.style.opacity = '0';
            }, halfDur);
        }
        else if (type === 'pixelate') {
            video.style.filter = `blur(${intensity * 12}px) contrast(200%) saturate(150%)`;
            if (overlay) {
                overlay.style.background = 'radial-gradient(rgba(0,0,0,0.4) 15%, transparent 16%) 0 0 / 8px 8px';
                overlay.style.opacity = `${intensity}`;
            }
            setTimeout(() => {
                video.style.filter = 'none';
                if (overlay) overlay.style.opacity = '0';
            }, halfDur);
        }
        else if (type === 'thermal_flash') {
            video.style.filter = 'invert(100%) hue-rotate(90deg) contrast(200%) saturate(300%)';
            setTimeout(() => {
                video.style.filter = 'none';
            }, halfDur);
        }
        else if (type === 'laser_streak') {
            if (overlay) {
                overlay.style.background = 'linear-gradient(180deg, transparent 40%, rgba(13, 202, 240, 0.95) 50%, rgba(255, 255, 255, 1) 51%, rgba(13, 202, 240, 0.95) 52%, transparent 60%)';
                overlay.style.opacity = '1';
            }
            video.style.filter = 'brightness(200%)';
            setTimeout(() => {
                video.style.filter = 'none';
                if (overlay) overlay.style.opacity = '0';
            }, halfDur);
        }
        else if (type === 'prism_split') {
            if (overlay) {
                overlay.style.background = 'linear-gradient(45deg, rgba(255,0,0,0.5), rgba(255,255,0,0.5), rgba(0,255,0,0.5), rgba(0,255,255,0.5), rgba(0,0,255,0.5), rgba(255,0,255,0.5))';
                overlay.style.opacity = `${intensity * 0.85}`;
            }
            video.style.filter = 'hue-rotate(90deg) saturate(250%)';
            setTimeout(() => {
                video.style.filter = 'none';
                if (overlay) overlay.style.opacity = '0';
            }, halfDur);
        }
    }

    // 7. Synthesized Sound FX Player (Web Audio API)
    function playSfx(type) {
        try {
            if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            const now = audioCtx.currentTime;

            if (type === 'bass_boom') {
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(140, now);
                osc.frequency.exponentialRampToValueAtTime(30, now + 1.2);
                gain.gain.setValueAtTime(0.8, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 1.2);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start(now);
                osc.stop(now + 1.2);
            } else if (type === 'laser_whoosh') {
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(800, now);
                osc.frequency.exponentialRampToValueAtTime(80, now + 0.4);
                gain.gain.setValueAtTime(0.5, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.4);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start(now);
                osc.stop(now + 0.4);
            } else if (type === 'vhs_glitch') {
                const bufferSize = audioCtx.sampleRate * 0.3;
                const buffer = audioCtx.createBuffer(1, bufferSize, audioCtx.sampleRate);
                const data = buffer.getChannelData(0);
                for (let i = 0; i < bufferSize; i++) data[i] = Math.random() * 2 - 1;
                const noise = audioCtx.createBufferSource();
                noise.buffer = buffer;
                const gain = audioCtx.createGain();
                gain.gain.setValueAtTime(0.4, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.3);
                noise.connect(gain);
                gain.connect(audioCtx.destination);
                noise.start(now);
            } else if (type === 'tension_riser') {
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(120, now);
                osc.frequency.linearRampToValueAtTime(600, now + 1.5);
                gain.gain.setValueAtTime(0.4, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 1.5);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start(now);
                osc.stop(now + 1.5);
            } else if (type === 'laser_hit') {
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(1200, now);
                osc.frequency.exponentialRampToValueAtTime(60, now + 0.35);
                gain.gain.setValueAtTime(0.7, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start(now);
                osc.stop(now + 0.35);
            } else if (type === 'footstep') {
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(80, now);
                osc.frequency.exponentialRampToValueAtTime(20, now + 0.2);
                gain.gain.setValueAtTime(0.9, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.2);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start(now);
                osc.stop(now + 0.2);
            }
        } catch(e) {
            console.error('Audio FX error:', e);
        }
    }

    // 8. 4K PNG Snapshot Grabber
    function takeSnapshot() {
        const video = document.getElementById('mainPlayer');
        if (!video) {
            alert('No video available to capture snapshot.');
            return;
        }
        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth || 1920;
        canvas.height = video.videoHeight || 1080;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        const a = document.createElement('a');
        a.download = `VOID_STILL_${Date.now()}.png`;
        a.href = canvas.toDataURL('image/png');
        a.click();
    }

    // 9. Live Video Transforms, Ken Burns & VFX
    function applyTransform() {
        const video = document.getElementById('mainPlayer');
        if (!video) return;
        const zoom = document.getElementById('zoomRange')?.value || 100;
        const posX = document.getElementById('posX')?.value || 0;
        const posY = document.getElementById('posY')?.value || 0;
        const rot = document.getElementById('rotateRange')?.value || 0;
        const opacity = document.getElementById('opacityRange')?.value || 100;

        document.getElementById('zoomValue').textContent = zoom + '%';
        document.getElementById('rotateValue').textContent = rot + '°';
        document.getElementById('opacityValue').textContent = opacity + '%';

        video.style.transform = `translate(${posX}px, ${posY}px) scale(${zoom/100}) rotate(${rot}deg)`;
        video.style.opacity = opacity / 100;
    }

    function resetTransform() {
        document.getElementById('zoomRange').value = 100;
        document.getElementById('posX').value = 0;
        document.getElementById('posY').value = 0;
        document.getElementById('rotateRange').value = 0;
        document.getElementById('opacityRange').value = 100;
        document.getElementById('kenBurnsPreset').value = 'none';
        const video = document.getElementById('mainPlayer');
        if (video) video.style.transition = 'none';
        applyTransform();
    }

    function applyKenBurns(preset) {
        const video = document.getElementById('mainPlayer');
        if (!video) return;
        video.style.transition = 'transform 12s cubic-bezier(0.25, 1, 0.5, 1)';
        
        if (preset === 'zoom_in') {
            video.style.transform = 'scale(1.25)';
        } else if (preset === 'zoom_out') {
            video.style.transform = 'scale(0.85)';
        } else if (preset === 'pan_lr') {
            video.style.transform = 'scale(1.15) translateX(30px)';
        } else if (preset === 'pan_rl') {
            video.style.transform = 'scale(1.15) translateX(-30px)';
        } else {
            video.style.transform = 'none';
        }
    }

    function updateVfxFilters() {
        const grainToggle = document.getElementById('vfxGrainToggle')?.checked;
        const grainStock = document.getElementById('vfxGrainStock')?.value || '35mm';
        const gateWeave = document.getElementById('vfxGateWeaveToggle')?.checked;
        const vignette = parseInt(document.getElementById('vfxVignetteRange')?.value || 0);
        const halation = parseInt(document.getElementById('vfxHalationRange')?.value || 0);
        const aberration = parseInt(document.getElementById('vfxAberrationRange')?.value || 0);
        const flare = document.getElementById('vfxFlareToggle')?.checked;
        const proMist = parseInt(document.getElementById('vfxProMistRange')?.value || 0);
        const tiltShift = document.getElementById('vfxTiltShiftToggle')?.checked;
        const crt = document.getElementById('vfxCrtToggle')?.checked;

        if (document.getElementById('vfxVignetteVal')) document.getElementById('vfxVignetteVal').textContent = vignette + '%';
        if (document.getElementById('vfxHalationVal')) document.getElementById('vfxHalationVal').textContent = halation + '%';
        if (document.getElementById('vfxAberrationVal')) document.getElementById('vfxAberrationVal').textContent = aberration + '%';
        if (document.getElementById('vfxProMistVal')) document.getElementById('vfxProMistVal').textContent = proMist + '%';

        const video = document.getElementById('mainPlayer');
        const container = document.getElementById('monitorViewport');
        const grainLayer = document.getElementById('vfxGrainLayer');
        const crtLayer = document.getElementById('vfxCrtLayer');
        const halationLayer = document.getElementById('vfxHalationLayer');
        const flareLayer = document.getElementById('vfxFlareLayer');
        const tiltShiftLayer = document.getElementById('vfxTiltShiftLayer');

        // Toggle Overlay Layers
        if (grainLayer) {
            grainLayer.className = grainToggle ? 'vfx-layer vfx-grain-active' : 'vfx-layer';
            if (grainToggle) {
                const size = grainStock === '8mm' ? '5px 5px' : (grainStock === '16mm' ? '3.5px 3.5px' : '2px 2px');
                const opacity = grainStock === '8mm' ? '0.85' : (grainStock === '16mm' ? '0.75' : '0.55');
                grainLayer.style.backgroundSize = size;
                grainLayer.style.opacity = opacity;
            }
        }

        if (crtLayer) {
            crtLayer.className = crt ? 'vfx-layer vfx-crt-active' : 'vfx-layer';
        }

        if (halationLayer) {
            halationLayer.className = halation > 0 ? 'vfx-layer vfx-halation-active' : 'vfx-layer';
            if (halation > 0) {
                halationLayer.style.boxShadow = `inset 0 0 ${halation * 1.3}px rgba(255, 45, 10, ${halation / 130})`;
            }
        }

        if (flareLayer) {
            flareLayer.className = flare ? 'vfx-layer vfx-flare-active' : 'vfx-layer';
        }

        if (tiltShiftLayer) {
            tiltShiftLayer.className = tiltShift ? 'vfx-layer vfx-tiltshift-active' : 'vfx-layer';
        }

        // Gate Weave Wobble Animation
        if (video) {
            if (gateWeave) {
                video.classList.add('gate-weave-active');
            } else {
                video.classList.remove('gate-weave-active');
            }
        }

        // Vignette Shadow on Monitor Viewport
        if (container) {
            if (vignette > 0) {
                container.style.boxShadow = `inset 0 0 ${vignette * 1.6}px rgba(0,0,0,0.95)`;
            } else {
                container.style.boxShadow = 'none';
            }
        }

        renderCompositeFilters();
    }

    function updateLetterboxMatte(val) {
        if (document.getElementById('vfxLetterboxVal')) document.getElementById('vfxLetterboxVal').textContent = val + '%';
        const top = document.getElementById('maskTop');
        const bottom = document.getElementById('maskBottom');
        if (top && bottom) {
            top.style.height = val + '%';
            bottom.style.height = val + '%';
        }
    }

    function setScopePreset(val) {
        const input = document.getElementById('vfxLetterboxRange');
        if (input) {
            input.value = val;
            updateLetterboxMatte(val);
        }
    }

    function resetVfxFilters() {
        if (document.getElementById('vfxGrainToggle')) document.getElementById('vfxGrainToggle').checked = false;
        if (document.getElementById('vfxGateWeaveToggle')) document.getElementById('vfxGateWeaveToggle').checked = false;
        if (document.getElementById('vfxVignetteRange')) document.getElementById('vfxVignetteRange').value = 0;
        if (document.getElementById('vfxHalationRange')) document.getElementById('vfxHalationRange').value = 0;
        if (document.getElementById('vfxAberrationRange')) document.getElementById('vfxAberrationRange').value = 0;
        if (document.getElementById('vfxFlareToggle')) document.getElementById('vfxFlareToggle').checked = false;
        if (document.getElementById('vfxProMistRange')) document.getElementById('vfxProMistRange').value = 0;
        if (document.getElementById('vfxTiltShiftToggle')) document.getElementById('vfxTiltShiftToggle').checked = false;
        if (document.getElementById('vfxCrtToggle')) document.getElementById('vfxCrtToggle').checked = false;
        if (document.getElementById('vfxLetterboxRange')) document.getElementById('vfxLetterboxRange').value = 0;
        updateVfxFilters();
        updateLetterboxMatte(0);
    }

    function applyVfxToAllScenes() {
        const toast = document.getElementById('transToast');
        const msg = document.getElementById('transToastMsg');
        if (toast && msg) {
            msg.innerHTML = `<strong>Cinematic VFX Suite</strong> applied to all ${document.querySelectorAll('.clip-video').length || 1} timeline clips!`;
            toast.classList.remove('d-none');
            setTimeout(() => toast.classList.add('d-none'), 3500);
        }
    }

    // 10. Color Grading Suite & 3D LUT Presets
    let currentLutPreset = 'none';

    const LUT_PRESETS = {
        'none': { name: 'Standard Rec.709', filter: '' },
        'kodak2383': { name: 'Kodak Vision3 2383', filter: 'sepia(18%) contrast(122%) brightness(102%) saturate(118%)' },
        'fuji_eterna': { name: 'Fujifilm Eterna 250D', filter: 'hue-rotate(15deg) contrast(115%) saturate(110%) brightness(104%)' },
        'teal_orange': { name: 'Teal & Orange', filter: 'contrast(135%) saturate(145%) hue-rotate(25deg) brightness(102%)' },
        'cyberpunk': { name: 'Blade Runner Cyberpunk', filter: 'hue-rotate(190deg) contrast(145%) saturate(180%) brightness(95%)' },
        'matrix': { name: 'The Matrix Green', filter: 'hue-rotate(75deg) saturate(160%) contrast(135%) brightness(98%)' },
        'bleach_bypass': { name: 'Bleach Bypass Silver', filter: 'saturate(55%) contrast(155%) brightness(96%)' },
        'dune_gold': { name: 'Dune Arrakis Gold', filter: 'sepia(35%) hue-rotate(-15deg) contrast(125%) saturate(135%) brightness(105%)' },
        'fincher': { name: 'Fincher Mood Green', filter: 'hue-rotate(50deg) saturate(75%) contrast(140%) brightness(88%)' },
        'noir': { name: 'Master Shadow Noir', filter: 'grayscale(100%) contrast(165%) brightness(92%)' },
        'kodachrome': { name: 'Kodachrome 64 Vintage', filter: 'sepia(20%) saturate(165%) contrast(128%) brightness(103%)' },
        'moonlight': { name: 'Cold Moonlight Blue', filter: 'hue-rotate(160deg) saturate(90%) contrast(130%) brightness(88%)' },
        'technicolor': { name: 'Technicolor 3-Strip', filter: 'saturate(220%) contrast(130%) brightness(102%)' }
    };

    function applyLut(type, el) {
        currentLutPreset = type;
        document.querySelectorAll('.lut-card').forEach(c => c.classList.remove('active'));
        if (el) el.classList.add('active');

        const label = document.getElementById('activeLutLabel');
        if (label && LUT_PRESETS[type]) {
            label.textContent = LUT_PRESETS[type].name;
        }

        renderCompositeFilters();
    }

    function updateColorGrade() {
        const exp = document.getElementById('cgExposure')?.value || 100;
        const con = document.getElementById('cgContrast')?.value || 100;
        const sat = document.getElementById('cgSaturation')?.value || 100;
        const temp = document.getElementById('cgTemperature')?.value || 0;
        const hue = parseInt(document.getElementById('cgHueRotation')?.value || 0);

        if (document.getElementById('cgExpVal')) document.getElementById('cgExpVal').textContent = (exp / 100).toFixed(2);
        if (document.getElementById('cgConVal')) document.getElementById('cgConVal').textContent = (con / 100).toFixed(2);
        if (document.getElementById('cgSatVal')) document.getElementById('cgSatVal').textContent = (sat / 100).toFixed(2);
        if (document.getElementById('cgTempVal')) document.getElementById('cgTempVal').textContent = (5600 + temp * 40) + 'K';
        if (document.getElementById('cgHueVal')) document.getElementById('cgHueVal').textContent = hue + '°';

        renderCompositeFilters();
    }

    function renderCompositeFilters() {
        const video = document.getElementById('mainPlayer');
        const colorVideo = document.getElementById('colorPlayer');

        const exp = document.getElementById('cgExposure')?.value || 100;
        const con = document.getElementById('cgContrast')?.value || 100;
        const sat = document.getElementById('cgSaturation')?.value || 100;
        const temp = document.getElementById('cgTemperature')?.value || 0;
        const hue = parseInt(document.getElementById('cgHueRotation')?.value || 0);

        const proMist = parseInt(document.getElementById('vfxProMistRange')?.value || 0);
        const aberration = parseInt(document.getElementById('vfxAberrationRange')?.value || 0);

        let filterParts = [];

        // 1. 3D LUT Base Filter
        if (LUT_PRESETS[currentLutPreset]?.filter) {
            filterParts.push(LUT_PRESETS[currentLutPreset].filter);
        }

        // 2. Primary Color Grade Adjustments
        const totalHue = hue + (temp * 0.4);
        filterParts.push(`brightness(${exp}%) contrast(${con}%) saturate(${sat}%)`);
        if (totalHue !== 0) {
            filterParts.push(`hue-rotate(${totalHue}deg)`);
        }

        // 3. Pro-Mist Soft Glow
        if (proMist > 0) {
            filterParts.push(`drop-shadow(0 0 ${proMist * 0.15}px rgba(255,255,255,${proMist / 200}))`);
        }

        const filterString = filterParts.join(' ').trim() || 'none';

        if (video) video.style.filter = filterString;
        if (colorVideo) colorVideo.style.filter = filterString;
    }

    function resetColorGrade() {
        if (document.getElementById('cgExposure')) document.getElementById('cgExposure').value = 100;
        if (document.getElementById('cgContrast')) document.getElementById('cgContrast').value = 100;
        if (document.getElementById('cgSaturation')) document.getElementById('cgSaturation').value = 100;
        if (document.getElementById('cgTemperature')) document.getElementById('cgTemperature').value = 0;
        if (document.getElementById('cgHueRotation')) document.getElementById('cgHueRotation').value = 0;
        applyLut('none', document.querySelector('.lut-card'));
        updateColorGrade();
    }

    function applyGradeToAllScenes() {
        const toast = document.getElementById('cgToast');
        const msg = document.getElementById('cgToastMsg');
        if (toast && msg) {
            const lutName = LUT_PRESETS[currentLutPreset]?.name || 'Rec.709';
            msg.innerHTML = `<strong>${lutName}</strong> grade batch-applied across all timeline scenes!`;
            toast.classList.remove('d-none');
            setTimeout(() => toast.classList.add('d-none'), 3500);
        }
    }

    // 11. Sidecar Subtitles (.SRT), EDL & Shotlist CSV Export
    function exportSRT() {
        let srt = '';
        const clips = document.querySelectorAll('[data-timeline-clip]');
        let index = 1;
        let curSec = 0;

        clips.forEach((c) => {
            const title = c.getAttribute('title') || '';
            const script = title.split(' · ')[1] || 'Dialogue segment';
            
            const startMin = String(Math.floor(curSec / 60)).padStart(2, '0');
            const startSec = String(Math.floor(curSec % 60)).padStart(2, '0');
            const endSecVal = curSec + 15;
            const endMin = String(Math.floor(endSecVal / 60)).padStart(2, '0');
            const endSec = String(Math.floor(endSecVal % 60)).padStart(2, '0');

            srt += `${index}\n00:${startMin}:${startSec},000 --> 00:${endMin}:${endSec},000\n${script}\n\n`;
            curSec = endSecVal;
            index++;
        });

        const blob = new Blob([srt], { type: 'text/plain' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = `project_subtitles.srt`;
        a.click();
    }

    function exportNleEDL() {
        let edl = `TITLE: VOID_STUDIO_EXPORT\nFCM: NON-DROP FRAME\n\n`;
        let curSec = 0;
        const clips = document.querySelectorAll('[data-timeline-clip]');
        
        clips.forEach((c, i) => {
            const inSec = curSec;
            const outSec = curSec + 15;
            curSec = outSec;
            
            const inTC = `01:00:${String(Math.floor(inSec)).padStart(2,'0')}:00`;
            const outTC = `01:00:${String(Math.floor(outSec)).padStart(2,'0')}:00`;
            const edlIndex = String(i + 1).padStart(3, '0');
            
            edl += `${edlIndex}  AX       V     C        00:00:00:00 00:00:15:00 ${inTC} ${outTC}\n`;
            edl += `* FROM CLIP: SCENE_${edlIndex}\n\n`;
        });

        const blob = new Blob([edl], { type: 'text/plain' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = `sequence.edl`;
        a.click();
    }

    function exportShotlistCSV() {
        let csv = "Sequence,Status,Duration,Prompt\n";
        const clips = document.querySelectorAll('[data-timeline-clip]');
        clips.forEach((c, i) => {
            const title = c.getAttribute('title') || '';
            const script = (title.split(' · ')[1] || '').replace(/"/g, '""');
            csv += `"${i+1}","ONLINE","15.00s","${script}"\n`;
        });

        const blob = new Blob([csv], { type: 'text/csv' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = `shotlist.csv`;
        a.click();
    }

    // 12. Generative Ambient Synthesizer in Fairlight
    function toggleNleAmbience() {
        if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        if (audioCtx.state === 'suspended') audioCtx.resume();

        const btn = document.getElementById('btnNleAmbience');
        const icon = document.getElementById('nleAmbienceIcon');
        const text = document.getElementById('nleAmbienceText');
        const preset = document.getElementById('nleAmbiencePreset')?.value || 'space';

        if (isNleAmbiencePlaying) {
            nleAmbienceOscillators.forEach(o => o.stop());
            nleAmbienceOscillators = [];
            isNleAmbiencePlaying = false;
            icon.className = 'bi bi-play-fill me-1';
            text.textContent = 'PLAY AMBIENCE';
            btn.className = 'btn btn-sm btn-info text-black font-mono py-0 px-2 fw-bold';
        } else {
            nleAmbienceGain = audioCtx.createGain();
            nleAmbienceGain.gain.setValueAtTime(0.25, audioCtx.currentTime);
            nleAmbienceGain.connect(audioCtx.destination);

            let freqs = [40, 80, 120];
            if (preset === 'noir') freqs = [55, 110, 164.81];
            if (preset === 'tension') freqs = [46.25, 92.50, 138.59];

            freqs.forEach(f => {
                const osc = audioCtx.createOscillator();
                osc.type = preset === 'noir' ? 'sawtooth' : 'sine';
                osc.frequency.setValueAtTime(f, audioCtx.currentTime);
                osc.connect(nleAmbienceGain);
                osc.start();
                nleAmbienceOscillators.push(osc);
            });

            isNleAmbiencePlaying = true;
            icon.className = 'bi bi-stop-fill me-1';
            text.textContent = 'STOP AMBIENCE';
            btn.className = 'btn btn-sm btn-danger text-white font-mono py-0 px-2 fw-bold';
        }
    }

    // 13. Audio & Metering
    function applyPlayerVolume() {
        const video = document.getElementById('mainPlayer');
        const state = document.getElementById('mixerState');
        if (!video) {
            if (state) { state.textContent = 'OFFLINE'; state.className = 'font-mono text-secondary'; }
            return;
        }

        const effectiveVolume = mixerMuted ? 0 : masterVolume * (mixerDim ? 0.35 : 1);
        video.volume = Math.max(0, Math.min(1, effectiveVolume));
        video.muted = mixerMuted;

        if (state) {
            state.textContent = mixerMuted ? 'MUTED' : (mixerDim ? 'DIM' : 'ONLINE');
            state.className = `font-mono ${mixerMuted ? 'text-danger' : (mixerDim ? 'text-warning' : 'text-success')}`;
        }
    }

    function setMasterVolume(value) {
        masterVolume = Number(value) / 100;
        document.getElementById('volumeReadout').textContent = `${(20 * Math.log10(Math.max(0.01, masterVolume))).toFixed(1)} dB / ${value}%`;
        applyPlayerVolume();
    }

    function toggleMute() {
        mixerMuted = !mixerMuted;
        document.getElementById('muteBtn')?.classList.toggle('active-muted', mixerMuted);
        applyPlayerVolume();
    }

    function toggleSolo() {
        mixerSolo = !mixerSolo;
        document.getElementById('soloBtn')?.classList.toggle('active-solo', mixerSolo);
    }

    function toggleDim() {
        mixerDim = !mixerDim;
        document.getElementById('dimBtn')?.classList.toggle('active-solo', mixerDim);
        applyPlayerVolume();
    }

    function updateMeters() {
        const video = document.getElementById('mainPlayer');
        if (!video || video.paused || mixerMuted) {
            setMeterHeight(4, 4);
            return;
        }
        const base = (35 + Math.sin(video.currentTime * 6) * 15 + Math.random() * 20) * masterVolume * (mixerDim ? 0.35 : 1);
        setMeterHeight(Math.min(92, base), Math.min(92, base * 0.95));
    }

    function setMeterHeight(left, right) {
        const meterLeft = document.getElementById('meterLeft');
        const meterRight = document.getElementById('meterRight');
        const peakLeft = document.getElementById('peakLeft');
        const peakRight = document.getElementById('peakRight');
        if (meterLeft) meterLeft.style.height = `${left}%`;
        if (meterRight) meterRight.style.height = `${right}%`;
        if (peakLeft) peakLeft.style.bottom = `${Math.max(4, left - 2)}%`;
        if (peakRight) peakRight.style.bottom = `${Math.max(4, right - 2)}%`;
    }

    function updateTimecode() {
        const video = document.getElementById('mainPlayer');
        const timecode = document.getElementById('timecode');
        if (!video || !timecode) return;
        const totalFrames = Math.floor(video.currentTime * 24);
        const seconds = Math.floor(totalFrames / 24);
        const frames = totalFrames % 24;
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        timecode.textContent = `01:${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}:${String(frames).padStart(2, '0')}`;
    }

    // 14. AI Enhancer Helpers
    function enhancePrompt() {
        const textarea = document.getElementById('aiPromptTextarea');
        if (!textarea) return;
        const suffixes = [
            ", 35mm anamorphic film, volumetric cinematic lighting, 8k masterpiece",
            ", moody high-contrast atmospheric haze, shallow depth of field, photorealistic",
            ", octane render, golden hour rim lighting, ultra detailed textures"
        ];
        textarea.value += suffixes[Math.floor(Math.random() * suffixes.length)];
    }

    function randomizeSeed() {
        const seedInput = document.getElementById('aiSeedInput');
        if (seedInput) seedInput.value = Math.floor(Math.random() * 99999999);
    }

    // 15. Timeline Tools & Zoom
    function activateTool(el) {
        document.querySelectorAll('.tool-icon[data-tool]').forEach(t => t.classList.remove('active'));
        el.classList.add('active');
    }

    function setTimelineZoom(val) {
        document.querySelectorAll('[data-timeline-clip]').forEach(clip => {
            clip.style.width = `${val}px`;
        });
    }

    function handleRulerClick(e) {
        const ruler = document.getElementById('timelineRuler');
        const rect = ruler.getBoundingClientRect();
        const clickX = e.clientX - rect.left - 150;
        if (clickX >= 0) {
            const playhead = document.getElementById('timelinePlayhead');
            if (playhead) playhead.style.left = `${clickX}px`;
        }
    }

    function addTimelineMarker() {
        alert('Marker added at current playhead timecode.');
    }

    function openShortcutsModal() {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('shortcutsModal')).show();
    }

    // Keyboard Shortcuts
    document.addEventListener('keydown', (e) => {
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) return;
        
        if (e.code === 'Space') { e.preventDefault(); togglePlay(); }
        else if (e.key === 'j' || e.key === 'J') { setSpeed(0.5); }
        else if (e.key === 'k' || e.key === 'K') { setSpeed(1.0); }
        else if (e.key === 'l' || e.key === 'L') { setSpeed(2.0); }
        else if (e.key === '←' || e.code === 'ArrowLeft') { e.preventDefault(); stepFrame(-1); }
        else if (e.key === '→' || e.code === 'ArrowRight') { e.preventDefault(); stepFrame(1); }
        else if (e.key === 'Home') { e.preventDefault(); seekToStart(); }
        else if (e.key === 'End') { e.preventDefault(); seekToEnd(); }
        else if (e.key === 'f' || e.key === 'F') { toggleFullscreen(); }
        else if (e.key === 'm' || e.key === 'M') { toggleMute(); }
        else if (e.key === '1') { switchWorkspace('edit'); }
        else if (e.key === '2') { switchWorkspace('color'); }
        else if (e.key === '3') { switchWorkspace('fairlight'); }
        else if (e.key === '4') { switchWorkspace('deliver'); }
        else if (e.key === '?') { openShortcutsModal(); }
    });

    document.addEventListener('DOMContentLoaded', () => {
        const video = document.getElementById('mainPlayer');
        if (video) {
            video.addEventListener('timeupdate', updateTimecode);
            video.addEventListener('play', () => {
                setPlayIcon(true);
                if (!meterTimer) meterTimer = window.setInterval(updateMeters, 100);
            });
            video.addEventListener('pause', () => {
                setPlayIcon(false);
                window.clearInterval(meterTimer);
                meterTimer = null;
                updateMeters();
            });
            video.addEventListener('ended', () => {
                setPlayIcon(false);
                window.clearInterval(meterTimer);
                meterTimer = null;
                updateMeters();
            });
            applyPlayerVolume();
            updateTimecode();
        }
        updateLiveTitle();
    });

document.querySelectorAll('[data-studio-click="0"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { takeSnapshot() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="1"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { document.getElementById('batchRenderForm').submit() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="2"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { seekToStart() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="3"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { stepFrame(-1) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="4"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { stepFrame(1) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="5"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { togglePlay() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="6"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { setSpeed(0.5) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="7"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { setSpeed(1.0) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="8"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { setSpeed(2.0) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="9"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { setScopeMask('16x9') } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="10"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { setScopeMask('anamorphic') } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="11"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { setScopeMask('vertical') } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="12"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { setScopeMask('classic43') } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="13"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { switchInspTab('ai'); switchWorkspace('edit'); } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="14"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { enhancePrompt() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="15"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { randomizeSeed() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="16"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { switchWorkspace('edit') } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="17"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { switchWorkspace('color') } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="18"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { switchWorkspace('fairlight') } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="19"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { switchWorkspace('deliver') } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="20"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { takeSnapshot() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="21"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { openShortcutsModal() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="22"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { switchInspTab('video') } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="23"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { switchInspTab('vfx') } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="24"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { switchInspTab('titles') } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="25"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { switchInspTab('transitions') } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="26"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { switchInspTab('ai') } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="27"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { resetTransform() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-input="28"]').forEach(element => element.addEventListener("input", function(event) { const result = (() => { applyTransform() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-input="29"]').forEach(element => element.addEventListener("input", function(event) { const result = (() => { applyTransform() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-input="30"]').forEach(element => element.addEventListener("input", function(event) { const result = (() => { applyTransform() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-input="31"]').forEach(element => element.addEventListener("input", function(event) { const result = (() => { applyTransform() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-input="32"]').forEach(element => element.addEventListener("input", function(event) { const result = (() => { applyTransform() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-change="33"]').forEach(element => element.addEventListener("change", function(event) { const result = (() => { applyKenBurns(this.value) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="34"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { setSpeed(0.25) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="35"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { setSpeed(0.5) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="36"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { setSpeed(1.0) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="37"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { setSpeed(1.5) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="38"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { setSpeed(2.0) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-change="39"]').forEach(element => element.addEventListener("change", function(event) { const result = (() => { updateVfxFilters() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-change="40"]').forEach(element => element.addEventListener("change", function(event) { const result = (() => { updateVfxFilters() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-change="41"]').forEach(element => element.addEventListener("change", function(event) { const result = (() => { updateVfxFilters() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-input="42"]').forEach(element => element.addEventListener("input", function(event) { const result = (() => { updateVfxFilters() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-input="43"]').forEach(element => element.addEventListener("input", function(event) { const result = (() => { updateVfxFilters() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-input="44"]').forEach(element => element.addEventListener("input", function(event) { const result = (() => { updateVfxFilters() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-change="45"]').forEach(element => element.addEventListener("change", function(event) { const result = (() => { updateVfxFilters() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-input="46"]').forEach(element => element.addEventListener("input", function(event) { const result = (() => { updateVfxFilters() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-change="47"]').forEach(element => element.addEventListener("change", function(event) { const result = (() => { updateVfxFilters() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-change="48"]').forEach(element => element.addEventListener("change", function(event) { const result = (() => { updateVfxFilters() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="49"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { setScopePreset(13) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="50"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { setScopePreset(6) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="51"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { setScopePreset(0) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-input="52"]').forEach(element => element.addEventListener("input", function(event) { const result = (() => { updateLetterboxMatte(this.value) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="53"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { resetVfxFilters() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="54"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { applyVfxToAllScenes() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-input="55"]').forEach(element => element.addEventListener("input", function(event) { const result = (() => { updateLiveTitle() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-input="56"]').forEach(element => element.addEventListener("input", function(event) { const result = (() => { updateLiveTitle() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-change="57"]').forEach(element => element.addEventListener("change", function(event) { const result = (() => { updateLiveTitle() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-change="58"]').forEach(element => element.addEventListener("change", function(event) { const result = (() => { updateLiveTitle() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-input="59"]').forEach(element => element.addEventListener("input", function(event) { const result = (() => { updateLiveTitle() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-input="60"]').forEach(element => element.addEventListener("input", function(event) { const result = (() => { updateLiveTitle() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-change="61"]').forEach(element => element.addEventListener("change", function(event) { const result = (() => { updateLiveTitle() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-input="62"]').forEach(element => element.addEventListener("input", function(event) { const result = (() => { filterTransitions() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="63"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { setTransCategory('all', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="64"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { setTransCategory('dissolve', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="65"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { setTransCategory('wipe', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="66"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { setTransCategory('motion', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="67"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { setTransCategory('cyber', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="68"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { setTransCategory('light', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="69"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { selectTransition('fade', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="70"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { selectTransition('flash', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="71"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { selectTransition('film_burn', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="72"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { selectTransition('bloom', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="73"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { selectTransition('color_dip', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="74"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { selectTransition('wipe', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="75"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { selectTransition('barn_door', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="76"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { selectTransition('radial_iris', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="77"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { selectTransition('diagonal_slice', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="78"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { selectTransition('venetian_blinds', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="79"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { selectTransition('zoom', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="80"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { selectTransition('zoom_out', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="81"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { selectTransition('vortex_spin', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="82"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { selectTransition('motion_blur_push', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="83"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { selectTransition('camera_shake', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="84"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { selectTransition('cube_flip', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="85"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { selectTransition('glitch', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="86"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { selectTransition('vhs_roll', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="87"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { selectTransition('pixelate', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="88"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { selectTransition('thermal_flash', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="89"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { selectTransition('laser_streak', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="90"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { selectTransition('prism_split', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-input="91"]').forEach(element => element.addEventListener("input", function(event) { const result = (() => { updateTransDurationVal(this.value) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="92"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { setTransDurationPreset(300) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="93"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { setTransDurationPreset(600) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="94"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { setTransDurationPreset(1000) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="95"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { setTransDurationPreset(2000) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-input="96"]').forEach(element => element.addEventListener("input", function(event) { const result = (() => { document.getElementById('transIntensityVal').textContent = this.value + '%' } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="97"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { playSelectedTransition() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="98"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { applyTransitionToClip('current') } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="99"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { applyTransitionToClip('all') } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="100"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { enhancePrompt() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="101"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { randomizeSeed() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-change="102"]').forEach(element => element.addEventListener("change", function(event) { const result = (() => { setScopeMask(this.value) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="103"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { takeSnapshot() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="104"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { toggleFullscreen() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="105"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { seekToStart() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="106"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { stepFrame(-1) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="107"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { togglePlay() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="108"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { stepFrame(1) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="109"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { seekToEnd() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="110"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { toggleLoop() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="111"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { toggleMute() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="112"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { toggleMute() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="113"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { toggleSolo() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="114"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { toggleDim() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-input="115"]').forEach(element => element.addEventListener("input", function(event) { const result = (() => { setMasterVolume(this.value) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="116"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { applyLut('none', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="117"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { applyLut('kodak2383', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="118"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { applyLut('fuji_eterna', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="119"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { applyLut('teal_orange', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="120"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { applyLut('cyberpunk', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="121"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { applyLut('matrix', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="122"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { applyLut('bleach_bypass', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="123"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { applyLut('dune_gold', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="124"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { applyLut('fincher', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="125"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { applyLut('noir', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="126"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { applyLut('kodachrome', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="127"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { applyLut('moonlight', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="128"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { applyLut('technicolor', this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-input="129"]').forEach(element => element.addEventListener("input", function(event) { const result = (() => { updateColorGrade() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-input="130"]').forEach(element => element.addEventListener("input", function(event) { const result = (() => { updateColorGrade() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-input="131"]').forEach(element => element.addEventListener("input", function(event) { const result = (() => { updateColorGrade() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-input="132"]').forEach(element => element.addEventListener("input", function(event) { const result = (() => { updateColorGrade() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-input="133"]').forEach(element => element.addEventListener("input", function(event) { const result = (() => { updateColorGrade() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="134"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { resetColorGrade() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="135"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { applyGradeToAllScenes() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="136"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { playSfx('bass_boom') } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="137"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { playSfx('laser_whoosh') } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="138"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { playSfx('vhs_glitch') } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="139"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { playSfx('tension_riser') } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="140"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { playSfx('laser_hit') } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="141"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { playSfx('footstep') } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="142"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { toggleNleAmbience() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="143"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { exportSRT() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="144"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { exportNleEDL() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="145"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { exportShotlistCSV() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="146"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { document.getElementById('batchRenderForm').requestSubmit() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="147"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { activateTool(this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="148"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { activateTool(this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="149"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { activateTool(this) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="150"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { this.classList.toggle('active') } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="151"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { this.classList.toggle('active') } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="152"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { addTimelineMarker() } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-input="153"]').forEach(element => element.addEventListener("input", function(event) { const result = (() => { setTimelineZoom(this.value) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="154"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { handleRulerClick(event) } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="155"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { this.classList.toggle('m-active') } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="156"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { this.classList.toggle('m-active') } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="157"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { this.classList.toggle('m-active') } )(); if (result === false) event.preventDefault(); }));

document.querySelectorAll('[data-studio-click="158"]').forEach(element => element.addEventListener("click", function(event) { const result = (() => { this.classList.toggle('s-active') } )(); if (result === false) event.preventDefault(); }));
