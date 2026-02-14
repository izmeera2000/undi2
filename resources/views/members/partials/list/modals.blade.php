<!-- Add Member Modal -->
<div class="modal fade" id="addMemberModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <div>
          <h5 class="modal-title">Add New Member</h5>
          <p class="text-muted small mb-0">Member will set their password on first login</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <form id="addMemberForm" enctype="multipart/form-data">
          <div class="text-center mb-4">
            <div class="avatar-upload">
              <div class="avatar-preview">
                <img src="{{ asset('assets/img/avatars/avatar-placeholder.webp') }}" alt="Avatar" id="avatarPreview">
              </div>
              <label class="avatar-edit" for="avatarUpload">
                <i class="bi bi-camera"></i>
                <input type="file" id="avatarUpload" name="profile_picture" accept="image/*" class="d-none">
              </label>
            </div>
          </div>

          <div class="row g-3">


            <div class="col-md-8">
              <label class="form-label">Full Name</label>
              <input type="text" name="nama" class="form-control" placeholder="Full Name" required>
            </div>
 
            <div class="col-md-4">
              <label class="form-label">Gender</label>
              <select name="jantina" class="form-select">
                <option value="">Select Gender…</option>
                <option value="Lelaki">Male</option>
                <option value="Perempuan">Female</option>
              </select>
            </div>

                        <div class="col-md-6">
              <label class="form-label">NO KP Baru</label>
              <input type="text" name="nokp_baru" class="form-control" placeholder="NOKP Baru">
            </div>
            <div class="col-md-6">
              <label class="form-label">NO KP Lama</label>
              <input type="text" name="nokp_lama" class="form-control" placeholder="NOKP Lama">
            </div>



            <div class="col-12">
              <label class="form-label">Address 1</label>
              <input type="text" name="alamat_1" class="form-control">
            </div>
            <div class="col-12">
              <label class="form-label">Address 2</label>
              <input type="text" name="alamat_2" class="form-control">
            </div>
            <div class="col-12">
              <label class="form-label">Address 3</label>
              <input type="text" name="alamat_3" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Poskod</label>
              <input type="text" name="poskod" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Bandar</label>
              <input type="text" name="bandar" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Negeri</label>
              <input type="text" name="negeri" class="form-control">
            </div>

            <div class="col-md-6">
              <label class="form-label">Bangsa</label>
              <input type="text" name="bangsa" class="form-control">
            </div>


            <div class="col-md-6">
              <label class="form-label">Alamat JPN 1</label>
              <input type="text" name="alamat_jpn_1" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Alamat JPN 2</label>
              <input type="text" name="alamat_jpn_2" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Alamat JPN 3</label>
              <input type="text" name="alamat_jpn_3" class="form-control">
            </div>


                        <div class="col-md-6">
              <label class="form-label">DUN ID</label>
              <input type="text" name="dun_id" class="form-control" placeholder="DUN ID">
            </div>
            <div class="col-md-6">
              <label class="form-label">Kod DM</label>
              <input type="text" name="kod_dm" class="form-control">
            </div>

            <div class="col-md-6">
              <label class="form-label">Kod CWGN</label>
              <input type="text" name="kod_cwgn" class="form-control" placeholder="Kod CWGN">
            </div>

            <div class="col-md-6">
              <label class="form-label">Nama CWGN</label>
              <input type="text" name="nama_cwgn" class="form-control" placeholder="Nama CWGN">
            </div>
            <div class="col-md-6">
              <label class="form-label">No Ahli</label>
              <input type="text" name="no_ahli" class="form-control" placeholder="No Ahli">
            </div>


          </div>
        </form>
      </div>

      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary" id="addMemberBtn">
          <i class="bi bi-plus-lg me-1"></i> Add Member
        </button>
      </div>
    </div>
  </div>
</div>
