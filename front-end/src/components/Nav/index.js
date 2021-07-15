import React from 'react';
import PropTypes from 'prop-types';
import { Dropdown, Icon, Menu } from 'semantic-ui-react';
import './style.scss';

const Nav = () => (
  <div className="nav fluid">
    <Menu icon="labeled" fluid compact>
      <Menu.Item
        name="Logo"
      >
        Logo
      </Menu.Item>
      <Menu.Item
        name="gamepad"
      >
        <Icon name="gamepad" />
        Games
      </Menu.Item>

      <Menu.Item
        name="avatar"
      >
        Avatar
      </Menu.Item>

      <Dropdown item icon="wrench" simple>
        <Dropdown.Menu>
          <Dropdown.Item>
            <span className="text">Mon compte</span>
          </Dropdown.Item>
          <Dropdown.Item>
            <span className="text">Mes collections</span>
          </Dropdown.Item>
          <Dropdown.Item>
            <Icon name="dropdown" />
            <span className="text">Thèmes</span>

            <Dropdown.Menu>
              <Dropdown.Item>Thème 1</Dropdown.Item>
              <Dropdown.Item>Thème 2</Dropdown.Item>
              <Dropdown.Item>Thème 3</Dropdown.Item>
              <Dropdown.Item>Thème 4</Dropdown.Item>
            </Dropdown.Menu>
          </Dropdown.Item>

        </Dropdown.Menu>
      </Dropdown>
      <Menu.Item
        name="Logout"

      >
        Logout
      </Menu.Item>
    </Menu>
  </div>
);

export default Nav;
