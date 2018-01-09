package com.irk.cacti;

import android.content.Context;
import android.content.Intent;
import android.support.design.widget.FloatingActionButton;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.MenuItem;
import android.view.View;
import android.view.ViewGroup;
import android.widget.AdapterView;
import android.widget.BaseAdapter;
import android.widget.ImageView;
import android.widget.ListView;
import android.widget.Spinner;
import android.widget.TextView;

import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.StringRequest;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

public class QuestionsActivity extends AppCompatActivity {
    private static final int ASK_INTENT = 0;

    MainApplication m;

    QuestionAdapter adapter;

    FloatingActionButton btnAsk;
    ListView lstQuestions;
    Spinner spnSubjects;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_questions);

        getSupportActionBar().setTitle(getIntent().getStringExtra("institution"));
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        getSupportActionBar().setBackgroundDrawable(getDrawable(R.drawable.red_toolbar));

        m = (MainApplication) getApplicationContext();

        btnAsk = (FloatingActionButton) findViewById(R.id.btnAsk);
        lstQuestions = (ListView) findViewById(R.id.lstQuestions);

        spnSubjects = (Spinner) findViewById(R.id.spnSubject);
        StringRequest subjectRequest = new StringRequest(m.SERVER_HOST + "/scripts/GetSubjects.php?institution=" + getIntent().getStringExtra("institution"), new Response.Listener<String>() {
            @Override
            public void onResponse(String response) {
                try {
                    JSONObject result = new JSONObject(response);

                    if(result.getBoolean("success")) {
                        SubjectAdapter adapter = new SubjectAdapter(result.getJSONArray("subjects"));
                        spnSubjects.setAdapter(adapter);
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
        StringRequest questionRequest = new StringRequest(m.SERVER_HOST + "/scripts/GetQuestions.php?institutionName=" + getIntent().getStringExtra("institution"), new Response.Listener<String>() {
            @Override
            public void onResponse(String response) {
                try {
                    JSONObject result = new JSONObject(response);

                    if(result.getBoolean("success")) {
                        adapter = new QuestionAdapter(result.getJSONArray("questions"));
                        lstQuestions.setAdapter(adapter);
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
        m.requestQueue.add(questionRequest);

        btnAsk.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent askIntent = new Intent(QuestionsActivity.this, AskActivity.class);
                askIntent.putExtra("institution", getIntent().getStringExtra("institution"));
                startActivityForResult(askIntent, ASK_INTENT);
            }
        });

        lstQuestions.setOnItemClickListener(new AdapterView.OnItemClickListener() {
            @Override
            public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
                int questionId = (int) adapter.getItemId(position);

                Intent viewIntent = new Intent(QuestionsActivity.this, ViewQuestionActivity.class);
                viewIntent.putExtra("questionId", questionId);
                startActivity(viewIntent);
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

    class QuestionAdapter extends BaseAdapter {
        JSONArray data;

        public QuestionAdapter(JSONArray data) {
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
                itemView = inflater.inflate(R.layout.list_question_item, parent, false);
            }
            else {
                itemView = convertView;
            }

            ImageView imgResolved = (ImageView) itemView.findViewById(R.id.imgResolved);
            TextView txtTitle = (TextView) itemView.findViewById(R.id.txtTitle);
            TextView txtDescription = (TextView) itemView.findViewById(R.id.txtDescription);
            TextView txtDate = (TextView) itemView.findViewById(R.id.txtDate);

            JSONObject o = (JSONObject) getItem(position);

            try {
                txtTitle.setText(o.getString("title"));
                txtDescription.setText(o.getString("description"));
                txtDate.setText(o.getString("askDate").split(" ")[0]);
                if(o.getInt("resolved") == 1) {
                    imgResolved.setImageResource(R.drawable.resolved);
                }
                else {
                    imgResolved.setImageResource(R.drawable.unresolved);
                }
            } catch (JSONException e) {
                e.printStackTrace();
            }
            return itemView;
        }
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if(requestCode == ASK_INTENT && resultCode == RESULT_OK) {
            StringRequest questionRequest = new StringRequest(m.SERVER_HOST + "/scripts/GetQuestions.php?institutionName=" + getIntent().getStringExtra("institution"), new Response.Listener<String>() {
                @Override
                public void onResponse(String response) {
                    try {
                        JSONObject result = new JSONObject(response);

                        if(result.getBoolean("success")) {
                            adapter = new QuestionAdapter(result.getJSONArray("questions"));
                            lstQuestions.setAdapter(adapter);
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
            m.requestQueue.add(questionRequest);
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
