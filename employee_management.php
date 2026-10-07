<?php
/*
Plugin Name: Employee Management
Description: A simple Employee Management WordPress plugin.
Version: 1.0.0
Author: Habiba
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/* =========================
   CREATE DATABASE TABLE
========================= */

function em_activate_plugin() {

    global $wpdb;

    $table_name = $wpdb->prefix . 'employees';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        employee_id varchar(50) NOT NULL,
        name varchar(100) NOT NULL,
        email varchar(100) NOT NULL,
        phone varchar(30) NOT NULL,
        department varchar(100) NOT NULL,
        position varchar(100) NOT NULL,
        joining_date date NOT NULL,
        salary decimal(10,2) NOT NULL,
        status varchar(20) NOT NULL DEFAULT 'Active',
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    dbDelta( $sql );
}

register_activation_hook( __FILE__, 'em_activate_plugin' );


/* =========================
   ADMIN MENU
========================= */

function em_add_admin_menu() {

    add_menu_page(
        'Employee Management',
        'Employees',
        'manage_options',
        'employee-management',
        'em_employee_page',
        'dashicons-groups',
        25
    );
}

add_action( 'admin_menu', 'em_add_admin_menu' );


/* =========================
   SAVE EMPLOYEE
========================= */

function em_save_employee() {

    if ( isset( $_POST['add_employee'] ) ) {

        global $wpdb;

        $table_name = $wpdb->prefix . 'employees';

        $employee_id  = sanitize_text_field( $_POST['employee_id'] );
        $name         = sanitize_text_field( $_POST['name'] );
        $email        = sanitize_email( $_POST['email'] );
        $phone        = sanitize_text_field( $_POST['phone'] );
        $department   = sanitize_text_field( $_POST['department'] );
        $position     = sanitize_text_field( $_POST['position'] );
        $joining_date = sanitize_text_field( $_POST['joining_date'] );
        $salary       = floatval( $_POST['salary'] );
        $status       = sanitize_text_field( $_POST['status'] );

        $wpdb->insert(
            $table_name,
            array(
                'employee_id'  => $employee_id,
                'name'         => $name,
                'email'        => $email,
                'phone'        => $phone,
                'department'   => $department,
                'position'     => $position,
                'joining_date' => $joining_date,
                'salary'       => $salary,
                'status'       => $status
            )
        );
    }
}

add_action( 'admin_init', 'em_save_employee' );

/* =========================
   EDIT EMPLOYEE
========================= */

function em_edit_employee() {

    if ( ! isset( $_POST['update_employee'] ) ) {
        return;
    }

    global $wpdb;

    $table_name = $wpdb->prefix . 'employees';

    $id = intval( $_POST['employee_db_id'] );

    $wpdb->update(
        $table_name,
        array(
            'employee_id'  => sanitize_text_field( $_POST['employee_id'] ),
            'name'         => sanitize_text_field( $_POST['name'] ),
            'email'        => sanitize_email( $_POST['email'] ),
            'phone'        => sanitize_text_field( $_POST['phone'] ),
            'department'   => sanitize_text_field( $_POST['department'] ),
            'position'     => sanitize_text_field( $_POST['position'] ),
            'joining_date' => sanitize_text_field( $_POST['joining_date'] ),
            'salary'       => floatval( $_POST['salary'] ),
            'status'       => sanitize_text_field( $_POST['status'] )
        ),
        array(
            'id' => $id
        )
    );

    wp_safe_redirect(
        admin_url( 'admin.php?page=employee-management&updated=1' )
    );

    exit;
}

add_action( 'admin_init', 'em_edit_employee' );


/* =========================
   EMPLOYEE PAGE
========================= */

// function em_employee_page() {

//     global $wpdb;

//     $table_name = $wpdb->prefix . 'employees';

//     $employees = $wpdb->get_results(
//         "SELECT * FROM $table_name ORDER BY id DESC"
//     );
function em_employee_page() {

    global $wpdb;

    $table_name = $wpdb->prefix . 'employees';

    // Check if we are editing an employee
    $edit_employee = null;

    if ( isset( $_GET['edit_id'] ) ) {

        $edit_id = intval( $_GET['edit_id'] );

        $edit_employee = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE id = %d",
                $edit_id
            )
        );
    }

    $employees = $wpdb->get_results(
        "SELECT * FROM $table_name ORDER BY id DESC"
    );

    ?>

    <div class="wrap">

        <h1>Employee Management</h1>

        <?php if ( isset( $_POST['add_employee'] ) ) : ?>

            <div class="notice notice-success is-dismissible">
                <p>Employee added successfully!</p>
            </div>

        <?php endif; ?>


        <h2>Add New Employee</h2>

        <form method="post">

            <table class="form-table">

                <tr>
                    <th>Employee ID</th>
                    <td>
                        <input type="text" name="employee_id" required>
                    </td>
                </tr>

                <tr>
                    <th>Name</th>
                    <td>
                        <input type="text" name="name" required>
                    </td>
                </tr>

                <tr>
                    <th>Email</th>
                    <td>
                        <input type="email" name="email" required>
                    </td>
                </tr>

                <tr>
                    <th>Phone</th>
                    <td>
                        <input type="text" name="phone" required>
                    </td>
                </tr>

                <tr>
                    <th>Department</th>
                    <td>
                        <input type="text" name="department" required>
                    </td>
                </tr>

                <tr>
                    <th>Position</th>
                    <td>
                        <input type="text" name="position" required>
                    </td>
                </tr>

                <tr>
                    <th>Joining Date</th>
                    <td>
                        <input type="date" name="joining_date" required>
                    </td>
                </tr>

                <tr>
                    <th>Salary</th>
                    <td>
                        <input type="number" name="salary" required>
                    </td>
                </tr>

                <tr>
                    <th>Status</th>
                    <td>

                        <select name="status">

                            <option value="Active">Active</option>

                            <option value="Inactive">Inactive</option>

                        </select>

                    </td>
                </tr>

            </table>

            <p>
                <input
                    type="submit"
                    name="add_employee"
                    value="Add Employee"
                    class="button button-primary"
                >
            </p>

        </form>


        <hr>


        <h2>Employee List</h2>

        <?php if ( $employees ) : ?>

            <table class="widefat striped">

                <thead>

                    <tr>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Department</th>
                        <th>Position</th>
                        <th>Joining Date</th>
                        <th>Salary</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>

                </thead>

                <tbody>

                    <?php foreach ( $employees as $employee ) : ?>

                        <tr>

                            <td>
                                <?php echo esc_html( $employee->employee_id ); ?>
                            </td>

                            <td>
                                <?php echo esc_html( $employee->name ); ?>
                            </td>

                            <td>
                                <?php echo esc_html( $employee->email ); ?>
                            </td>

                            <td>
                                <?php echo esc_html( $employee->phone ); ?>
                            </td>

                            <td>
                                <?php echo esc_html( $employee->department ); ?>
                            </td>

                            <td>
                                <?php echo esc_html( $employee->position ); ?>
                            </td>

                            <td>
                                <?php echo esc_html( $employee->joining_date ); ?>
                            </td>

                            <td>
                                <?php echo esc_html( $employee->salary ); ?>
                            </td>

                            <td>
                                <?php echo esc_html( $employee->status ); ?>
                            </td>
                            <td>

    <a
    href="<?php echo admin_url( 'admin.php?page=employee-management&edit_id=' . $employee->id ); ?>"
    class="button"
>
    Edit
</a>

    <a href="#" class="button button-link-delete">
        Delete
    </a>

</td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        <?php else : ?>

            <p>No employees found.</p>

        <?php endif; ?>

    </div>

    <?php
}