@extends('admin.layouts.adminlayout')
@section('title', isset($user) ? 'Edit User' : 'Create User')

@section('content')
<div class="container py-3">
    <div class="d-flex justify-content-between align-items-center mb-3 p-2 rounded" style="background:#00284d;color:#fff;">
        <h4 class="mb-0">{{ isset($user) ? 'Edit User' : 'Create User' }}</h4>
        <a href="{{ route('admin.users.index') }}" class="btn btn-light btn-sm">Back</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ isset($user) ? route('admin.users.update', $user->id) : route('admin.users.store') }}" method="POST">
                @csrf
                @if(isset($user))
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Login ID (Card)</label>
                        <input type="text" name="login_id" class="form-control" value="{{ old('login_id', $user->login_id ?? '') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Department</label>
                        <select name="department" class="form-select" required>
                            <option value="">Select Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department', $user->department ?? '') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">User Type</label>
                        <select name="user_type" class="form-select" required>
                            <option value="admin" {{ old('user_type', $user->user_type ?? '') === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="staff" {{ old('user_type', $user->user_type ?? '') === 'staff' ? 'selected' : '' }}>Staff</option>
                        </select>
                    </div>

                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                {{ old('is_active', isset($user) ? (bool) $user->is_active : true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active User</label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Password {{ isset($user) ? '(Keep blank to keep current)' : '' }}</label>
                        <input type="password" name="password" class="form-control" {{ isset($user) ? '' : 'required' }}>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control" {{ isset($user) ? '' : 'required' }}>
                    </div>

                    <div class="col-12">
                        <hr>
                        <h5 class="mb-2">Face Enrollment (Optional)</h5>
                        <p class="text-muted mb-3">Use this for admin/staff face login. Login ID + face will be required for face login mode.</p>
                        <input type="hidden" name="face_descriptor" id="face_descriptor" value="{{ old('face_descriptor', isset($user) && is_array($user->face_descriptor) ? json_encode($user->face_descriptor) : '') }}">

                        <div class="row g-3">
                            <div class="col-lg-8">
                                <video id="faceVideo" class="w-100 rounded border bg-dark" autoplay muted playsinline style="min-height:260px; object-fit: cover;"></video>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-grid gap-2">
                                    <button type="button" id="startFaceCameraBtn" class="btn btn-outline-primary">Start Camera</button>
                                    <button type="button" id="captureFaceDescriptorBtn" class="btn btn-success">Capture Face</button>
                                    <button type="button" id="clearFaceDescriptorBtn" class="btn btn-outline-danger">Clear Face</button>
                                </div>
                                <div id="faceStatus" class="small mt-3 text-muted">No face captured yet.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-warning fw-bold">{{ isset($user) ? 'Update User' : 'Create User' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
    (function () {
        const video = document.getElementById('faceVideo');
        const startBtn = document.getElementById('startFaceCameraBtn');
        const captureBtn = document.getElementById('captureFaceDescriptorBtn');
        const clearBtn = document.getElementById('clearFaceDescriptorBtn');
        const descriptorInput = document.getElementById('face_descriptor');
        const statusBox = document.getElementById('faceStatus');
        const modelPath = '/models';
        let stream = null;
        let modelsLoaded = false;

        function status(message, type = 'muted') {
            statusBox.textContent = message;
            statusBox.className = 'small mt-3';
            if (type === 'success') {
                statusBox.classList.add('text-success');
                return;
            }
            if (type === 'error') {
                statusBox.classList.add('text-danger');
                return;
            }
            statusBox.classList.add('text-muted');
        }

        async function loadModels() {
            if (modelsLoaded) {
                return;
            }
            if (!window.faceapi) {
                throw new Error('face-api.js failed to load.');
            }

            status('Loading face models...');
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(modelPath),
                faceapi.nets.faceLandmark68Net.loadFromUri(modelPath),
                faceapi.nets.faceRecognitionNet.loadFromUri(modelPath),
            ]);
            modelsLoaded = true;
            status('Models loaded. Start camera.', 'success');
        }

        async function startCamera() {
            try {
                await loadModels();
                stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
                video.srcObject = stream;
                await video.play();
                status('Camera started. Keep one face centered.', 'success');
            } catch (error) {
                status(error.message || 'Unable to start camera.', 'error');
            }
        }

        async function captureFace() {
            try {
                if (!video.srcObject) {
                    status('Start camera first.', 'error');
                    return;
                }

                const tinyOptions = new faceapi.TinyFaceDetectorOptions();
                const samples = [];
                const targetSamples = 5;
                const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

                for (let i = 0; i < targetSamples; i++) {
                    status(`Capturing face sample ${i + 1}/${targetSamples}...`);

                    const allFaces = await faceapi.detectAllFaces(video, tinyOptions);
                    if (allFaces.length === 0) {
                        status('No face detected. Keep face centered and retry.', 'error');
                        return;
                    }
                    if (allFaces.length > 1) {
                        status('Multiple faces detected. Keep only one face in frame.', 'error');
                        return;
                    }

                    const detection = await faceapi
                        .detectSingleFace(video, tinyOptions)
                        .withFaceLandmarks()
                        .withFaceDescriptor();

                    if (!detection || !detection.descriptor) {
                        status('Could not capture descriptor. Try better lighting.', 'error');
                        return;
                    }

                    samples.push(Array.from(detection.descriptor));
                    await wait(180);
                }

                const averaged = new Array(128).fill(0);
                samples.forEach((sample) => {
                    for (let i = 0; i < 128; i++) {
                        averaged[i] += sample[i];
                    }
                });
                for (let i = 0; i < 128; i++) {
                    averaged[i] /= samples.length;
                }

                descriptorInput.value = JSON.stringify(averaged);
                status('Face descriptor captured (5-sample average) and will be saved.', 'success');
            } catch (error) {
                status(error.message || 'Face capture failed.', 'error');
            }
        }

        function clearFace() {
            descriptorInput.value = '';
            status('Face descriptor cleared. Save form to apply.', 'muted');
        }

        if (descriptorInput.value) {
            status('Existing face descriptor is set for this user.', 'success');
        }

        startBtn.addEventListener('click', startCamera);
        captureBtn.addEventListener('click', captureFace);
        clearBtn.addEventListener('click', clearFace);
    })();
</script>
@endpush
