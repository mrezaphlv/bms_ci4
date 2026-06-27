<style>
    .layout-shell {
        background: #fff;
    }
    .sidebar-panel {
        background: #23232B;
        color: #fff;
        padding-top: 8px;
    }
    .sidebar-profile {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 12px 16px;
    }
    .sidebar-user {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }
    .sidebar-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        object-fit: cover;
        cursor: pointer;
    }
    .sidebar-name {
        color: #fff;
        font-size: 14px;
        line-height: 1.2;
        cursor: pointer;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .logout-link {
        color: #fff;
        font-size: 20px;
        text-decoration: none;
    }
    #dropdownMenu {
        display: none;
        position: absolute;
        top: 52px;
        left: 12px;
        right: 44px;
        background: rgba(255, 255, 255, 0.92);
        border-radius: 6px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
        overflow: hidden;
        z-index: 999;
    }
    #dropdownMenu a {
        display: block;
        padding: 10px 12px;
        color: #23232B;
        text-decoration: none;
    }
    #dropdownMenu a:hover {
        background: #fff;
        color: #2857c5;
    }
    .tabs-container {
        width: 100%;
        height: 100%;
    }
    .home-card {
        background: linear-gradient(135deg, #23232B 0%, #3f4a2f 100%);
        color: #fff;
        border-radius: 18px;
        padding: 28px;
        box-shadow: 0 18px 40px rgba(35, 35, 43, 0.16);
    }
    .home-card h2 {
        margin: 0 0 8px;
        font-size: 28px;
        font-weight: 600;
    }
    .home-card p {
        margin: 0;
        max-width: 560px;
        opacity: 0.92;
        line-height: 1.6;
    }
    .home-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
        margin-top: 18px;
    }
    .home-stat {
        background: #fff;
        border: 1px solid #e8e8e8;
        border-radius: 14px;
        padding: 18px;
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.05);
    }
    .home-stat .label {
        color: #6f6f6f;
        font-size: 12px;
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }
    .home-stat .value {
        color: #23232B;
        font-size: 20px;
        font-weight: 600;
    }
    .panel-body,
    .tabs-panels,
    .layout-panel-center > .panel-body {
        background: #f5f6f8;
    }
</style>

<div data-options="region:'center'" class="layout-shell">
    <div class="easyui-layout" data-options="fit:true">
        <div data-options="region:'west',split:false,collapsed:true" title="COMMERCIAL AREA" style="width:240px;" class="sidebar-panel">
            <div class="sidebar-profile">
                <div class="sidebar-user" onclick="toggleDropdown()">
                    <img src="<?= esc($url_sso) ?>/assets/img/avatar-2.svg" alt="Profile" class="sidebar-avatar">
                    <span class="sidebar-name" title="<?= esc($nama) ?>"><?= esc($username) ?></span>
                </div>
                <a href="<?= site_url('logout') ?>" class="logout-link" title="Logout">
                    <i class="fa fa-sign-out" aria-hidden="true"></i>
                </a>
                <div id="dropdownMenu">
                    <a href="<?= esc($url_sso) ?>?tkn=<?= urlencode($token) ?>&app=<?= urlencode(encrypt($id_apps)) ?>&id=<?= session_id() ?>" target="_blank" rel="noopener">My Profile</a>
                </div>
            </div>
            <div id="menu"></div>
        </div>
        <div data-options="region:'center'">
            <div id="tabs" class="easyui-tabs tabs-container">
                <div title="Home" style="padding:18px;">
                    <div class="home-card">
                        <h2>Welcome back, <?= esc($nama !== '' ? $nama : $username) ?></h2>
                        <p>Anda sudah berhasil login ke BMS. Halaman ini dibuat mengikuti pola layout EasyUI `comm_area` dan saat ini dibatasi hanya untuk kebutuhan login, home/dashboard, dan logout.</p>
                    </div>
                    <div class="home-grid">
                        <div class="home-stat">
                            <div class="label">Login Status</div>
                            <div class="value">Authenticated</div>
                        </div>
                        <div class="home-stat">
                            <div class="label">Display Name</div>
                            <div class="value"><?= esc($nama !== '' ? $nama : '-') ?></div>
                        </div>
                        <div class="home-stat">
                            <div class="label">Username</div>
                            <div class="value"><?= esc($username !== '' ? $username : '-') ?></div>
                        </div>
                        <div class="home-stat">
                            <div class="label">Email</div>
                            <div class="value"><?= esc($email !== '' ? $email : '-') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleDropdown() {
        const menu = document.getElementById('dropdownMenu');
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    }

    document.addEventListener('click', function (event) {
        const profile = document.querySelector('.sidebar-user');
        const dropdown = document.getElementById('dropdownMenu');
        if (profile && dropdown && !profile.contains(event.target) && !dropdown.contains(event.target)) {
            dropdown.style.display = 'none';
        }
    });

    $(function () {
        $('#tabs').tabs();
        $('#menu').tree({
            data: [
                {
                    text: 'Main',
                    state: 'open',
                    children: [
                        {
                            text: 'Home',
                            iconCls: 'icon-tip',
                            url: '<?= site_url('dashboard') ?>'
                        }
                    ]
                }
            ],
            onClick: function (node) {
                if (node.url) {
                    window.location.href = node.url;
                }
            }
        });
        $('#menu').tree('collapseAll');
        $('#menu').tree('expandTo', $('#menu').tree('getRoot').target);
    });
</script>
