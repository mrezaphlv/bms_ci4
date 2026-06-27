
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
                <div title="Home" style="padding:10px;">
                    <h3><i class="fa fa-home" style="font-size:24px;"></i></h3>
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
