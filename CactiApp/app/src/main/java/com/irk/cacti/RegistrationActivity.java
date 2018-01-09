package com.irk.cacti;

import android.app.ProgressDialog;
import android.content.Intent;
import android.support.v7.app.AlertDialog;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.MenuItem;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.Toast;

import com.android.volley.AuthFailureError;
import com.android.volley.Request;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.StringRequest;
import com.android.volley.toolbox.Volley;

import org.json.JSONException;
import org.json.JSONObject;

import java.util.HashMap;
import java.util.Map;

public class RegistrationActivity extends AppCompatActivity {
    private static final int JOIN_INSTITUTION_INTENT = 0;

    MainApplication m;

    EditText edtName, edtUsername, edtPassword, edtConfirmPassword, edtEmail;
    Button btnRegister;
    ImageView btnBack;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_registration);

        getSupportActionBar().hide();

        m = (MainApplication) getApplicationContext();

        if(m.requestQueue == null) {
            m.requestQueue = Volley.newRequestQueue(RegistrationActivity.this);
        }

        edtName = (EditText) findViewById(R.id.edtName);
        edtUsername = (EditText) findViewById(R.id.edtUsername);
        edtPassword = (EditText) findViewById(R.id.edtPassword);
        edtConfirmPassword = (EditText) findViewById(R.id.edtConfirmPassword);
        edtEmail = (EditText) findViewById(R.id.edtEmail);

        btnBack = (ImageView) findViewById(R.id.btnBack);
        btnRegister = (Button) findViewById(R.id.btnRegister);

        btnBack.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                finish();
            }
        });

        btnRegister.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                final String name = edtName.getText().toString();
                final String username = edtUsername.getText().toString();
                final String password = edtPassword.getText().toString();
                final String confirmPassword = edtConfirmPassword.getText().toString();
                final String email = edtEmail.getText().toString();

                if(name.isEmpty()) {
                    edtName.setError("Please enter your name");
                    return;
                }

                if(username.isEmpty()) {
                    edtUsername.setError("Please enter a username");
                    return;
                }

                if(password.isEmpty()) {
                    edtPassword.setError("Please enter a password");
                    return;
                }

                if(confirmPassword.isEmpty()) {
                    edtConfirmPassword.setError("Please confirm your password");
                    return;
                }

                if(!password.equals(confirmPassword)) {
                    edtConfirmPassword.setError("Passwords do not match");
                    return;
                }

                if(email.isEmpty()) {
                    edtEmail.setError("Please enter your email");
                    return;
                }

                final ProgressDialog builder = new ProgressDialog(RegistrationActivity.this);
                builder.setTitle("Registering you");
                builder.setMessage("Please wait while we register your account");
                builder.setCancelable(false);
                builder.setIndeterminate(true);
                builder.show();

                StringRequest registerRequest = new StringRequest(Request.Method.POST, m.SERVER_HOST + "/scripts/Registration.php", new Response.Listener<String>() {
                    @Override
                    public void onResponse(String response) {
                        builder.cancel();

                        try {
                            JSONObject result = new JSONObject(response);

                            if(!result.getBoolean("success")) {
                                Toast.makeText(RegistrationActivity.this, result.getString("message"), Toast.LENGTH_LONG).show();
                            }
                            else {
                                m.userId = result.getInt("userId");
                                Intent joinInstitutionIntent = new Intent(RegistrationActivity.this, JoinInstitutionActivity.class);
                                startActivityForResult(joinInstitutionIntent, JOIN_INSTITUTION_INTENT);
                            }
                        } catch (JSONException e) {
                            e.printStackTrace();
                        }
                    }
                }, new Response.ErrorListener() {
                    @Override
                    public void onErrorResponse(VolleyError error) {
                        Toast.makeText(RegistrationActivity.this, error.getMessage(), Toast.LENGTH_LONG).show();
                    }
                }) {
                    @Override
                    protected Map<String,String> getParams(){
                        Map<String,String> params = new HashMap<>();
                        params.put("username", username);
                        params.put("password",password);
                        params.put("confirmPassword", confirmPassword);
                        params.put("name", name);
                        params.put("email", email);

                        return params;
                    }

                    @Override
                    public Map<String, String> getHeaders() throws AuthFailureError {
                        Map<String,String> params = new HashMap<>();
                        params.put("Content-Type","application/x-www-form-urlencoded");
                        return params;
                    }
                };

                m.requestQueue.add(registerRequest);
            }
        });
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);

        if(requestCode == JOIN_INSTITUTION_INTENT) {
            setResult(RESULT_OK);
            finish();
        }
    }
}
