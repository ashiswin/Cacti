package com.irk.cacti;

import android.content.Context;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.MenuItem;
import android.view.View;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.Spinner;
import android.widget.TextView;
import android.widget.Toast;

import com.android.volley.AuthFailureError;
import com.android.volley.Request;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.StringRequest;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.HashMap;
import java.util.Map;

public class AskActivity extends AppCompatActivity {
    MainApplication m;

    SubjectAdapter adapter;

    Button btnSubmit;
    EditText edtTitle, edtDescription;
    Spinner spnSubject;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_ask);

        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        getSupportActionBar().setBackgroundDrawable(getDrawable(R.drawable.red_toolbar));

        m = (MainApplication) getApplicationContext();

        edtTitle = (EditText) findViewById(R.id.edtTitle);
        edtDescription = (EditText) findViewById(R.id.edtDescription);
        spnSubject = (Spinner) findViewById(R.id.spnSubject);

        btnSubmit = (Button) findViewById(R.id.btnSubmit);

        StringRequest subjectRequest = new StringRequest(m.SERVER_HOST + "/scripts/GetSubjects.php?institution=" + getIntent().getStringExtra("institution"), new Response.Listener<String>() {
            @Override
            public void onResponse(String response) {
                try {
                    JSONObject result = new JSONObject(response);

                    if(result.getBoolean("success")) {
                        adapter = new SubjectAdapter(result.getJSONArray("subjects"));
                        spnSubject.setAdapter(adapter);
                    }
                } catch (JSONException e) {
                    e.printStackTrace();
                }
            }
        }, new Response.ErrorListener() {
            @Override
            public void onErrorResponse(VolleyError error) {

            }
        });

        m.requestQueue.add(subjectRequest);

        btnSubmit.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                final String title = edtTitle.getText().toString();
                final String description = edtDescription.getText().toString();

                if(title.isEmpty()) {
                    edtTitle.setError("Please enter the title");
                    return;
                }
                if(description.isEmpty()) {
                    edtDescription.setError("Please enter a description");
                    return;
                }

                final int subjectId = (int) spnSubject.getSelectedItemId();

                StringRequest askRequest = new StringRequest(Request.Method.POST, m.SERVER_HOST + "/scripts/AskQuestion.php", new Response.Listener<String>() {
                    @Override
                    public void onResponse(String response) {
                        try {
                            JSONObject result = new JSONObject(response);

                            if(result.getBoolean("success")) {
                                setResult(RESULT_OK);
                                finish();
                            }
                            else {
                                Toast.makeText(AskActivity.this, result.getString("message"), Toast.LENGTH_LONG).show();
                            }
                        } catch (JSONException e) {
                            e.printStackTrace();
                        }
                    }
                }, new Response.ErrorListener() {
                    @Override
                    public void onErrorResponse(VolleyError error) {

                    }
                }) {
                    @Override
                    protected Map<String,String> getParams(){
                        Map<String,String> params = new HashMap<>();
                        params.put("userId", m.userId + "");
                        params.put("title", title);
                        params.put("description", description);
                        params.put("subject", subjectId + "");
                        params.put("institution", getIntent().getStringExtra("institution"));

                        return params;
                    }

                    @Override
                    public Map<String, String> getHeaders() throws AuthFailureError {
                        Map<String,String> params = new HashMap<>();
                        params.put("Content-Type","application/x-www-form-urlencoded");
                        return params;
                    }
                };

                m.requestQueue.add(askRequest);
            }
        });
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        // Handle item selection
        switch (item.getItemId()) {
            case android.R.id.home:
                finish();
            default:
                return super.onOptionsItemSelected(item);
        }
    }

    class SubjectAdapter extends BaseAdapter {
        JSONArray data;

        public SubjectAdapter(JSONArray data) {
            this.data = data;
        }

        @Override
        public int getCount() {
            return data.length();
        }

        @Override
        public Object getItem(int position) {
            try {
                return data.getJSONObject(position);
            } catch (JSONException e) {
                e.printStackTrace();

                return null;
            }
        }

        @Override
        public long getItemId(int position) {
            try {
                return data.getJSONObject(position).getInt("id");
            } catch (JSONException e) {
                e.printStackTrace();
                return 0;
            }
        }

        @Override
        public View getView(int position, View convertView, ViewGroup parent) {
            View itemView;

            if(convertView == null) {
                LayoutInflater inflater = (LayoutInflater) getSystemService(Context.LAYOUT_INFLATER_SERVICE);
                itemView = inflater.inflate(android.R.layout.simple_dropdown_item_1line, parent, false);
            }
            else {
                itemView = convertView;
            }

            TextView text = (TextView) itemView.findViewById(android.R.id.text1);

            JSONObject o = (JSONObject) getItem(position);

            try {
                text.setText(o.getString("name"));
            } catch (JSONException e) {
                e.printStackTrace();
            }
            return itemView;
        }
    }
}
